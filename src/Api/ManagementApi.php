<?php declare(strict_types=1);

namespace Torr\Storyblok\Api;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\HttpOptions;
use Symfony\Component\HttpClient\RetryableHttpClient;
use Symfony\Component\RateLimiter\LimiterInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Torr\Cli\Console\Style\TorrStyle;
use Torr\Storyblok\Api\Data\ApiActionPerformed;
use Torr\Storyblok\Api\Data\ComponentIdMap;
use Torr\Storyblok\Config\StoryblokConfig;
use Torr\Storyblok\Exception\Api\ApiRequestFailedException;
use Torr\Storyblok\Exception\Api\DatasourceSyncFailedException;
use Torr\Storyblok\Folder\FolderData;

final class ManagementApi
{
	private const API_URL = "https://mapi.storyblok.com/v1/spaces/%d/";
	private readonly HttpClientInterface $client;
	private ?ComponentIdMap $componentIdMap = null;
	private readonly LimiterInterface $rateLimiter;

	/**
	 */
	public function __construct (
		private readonly StoryblokConfig $config,
		HttpClientInterface $client,
		RateLimiterFactory $storyblokManagementLimiter,
		readonly LoggerInterface $logger,
	)
	{
		$this->rateLimiter = $storyblokManagementLimiter->create();
		$this->client = new RetryableHttpClient(
			$client->withOptions(
				(new HttpOptions())
					->setBaseUri(\sprintf(self::API_URL, $this->config->getSpaceId()))
					->toArray(),
			),
		);
	}

	/**
	 * @throws ApiRequestFailedException
	 */
	public function syncComponent (
		array $config,
		string|\BackedEnum|null $componentGroupLabel = null,
	) : ApiActionPerformed
	{
		$componentIdMap = $this->getComponentIdMap();
		$config["component_group_uuid"] = $this->getOrCreatedComponentGroupUuid($componentGroupLabel);

		$options = (new HttpOptions())
			->setJson([
				"component" => $config,
			]);

		$componentId = $this->getComponentIdMap()->getComponentId($config["name"]);

		$data = null !== $componentId
			? $this->sendRequest("components/{$componentId}", $options, "PUT")
			: $this->sendRequest("components", $options, "POST");

		// add id to component id map
		$componentIdMap->registerComponent($data["component"]["name"], $data["component"]["id"]);

		return null !== $componentId
			? ApiActionPerformed::UPDATED
			: ApiActionPerformed::ADDED;
	}

	/**
	 * Gets or creates a component group uuid
	 */
	private function getOrCreatedComponentGroupUuid (string|\BackedEnum|null $name) : ?string
	{
		if (null === $name)
		{
			return null;
		}

		if ($name instanceof \BackedEnum)
		{
			$name = (string) $name->value;
		}

		$idMap = $this->getComponentIdMap();

		if (null !== ($existingUuid = $idMap->getGroupUuid($name)))
		{
			return $existingUuid;
		}

		// ensure that we stay in the rate limit
		$this->rateLimiter->consume()->wait();

		$data = $this->sendRequest(
			"component_groups",
			options: (new HttpOptions())
				->setJson([
					"component_group" => [
						"name" => $name,
					],
				]),
			method: "POST",
		);

		$uuid = $data["component_group"]["uuid"];
		$idMap->registerComponentGroup($name, $uuid);

		return $uuid;
	}

	/**
	 * Fetches the map of local url to folder name
	 *
	 * @return array<string, string> Map of local url to title
	 */
	public function fetchFolderTitleMap (string $slugPrefix) : array
	{
		// include the trailing slash, to exclude the base directory
		$slugPrefix = \trim($slugPrefix, "/") . "/";

		$options = (new HttpOptions())
			->setQuery([
				"folder_only" => true,
				"starts_with" => $slugPrefix,
				"per_page" => 100,
			]);

		$map = [];
		$replacement = "~" . \preg_quote($slugPrefix, "~") . "~";

		$data = $this->sendRequest("stories", $options);
		$stories = $data["stories"] ?? [];

		// @todo paginate here
		foreach ($stories as $entry)
		{
			// use heading slash to local url
			$localSlug = "/" . \preg_replace($replacement, "", $entry["full_slug"]);
			$map[$localSlug] = $entry["name"];
		}

		return $map;
	}

	/**
	 * Fetches all folders in a given slug path
	 *
	 * @return array<FolderData>
	 */
	public function fetchFoldersInPath (string $slugPrefix) : array
	{
		// include the trailing slash, to exclude the base directory
		$slugPrefix = \trim($slugPrefix, "/") . "/";

		$options = (new HttpOptions())
			->setQuery([
				"folder_only" => true,
				"starts_with" => $slugPrefix,
				"per_page" => 100,
			]);

		$response = $this->sendRequest("stories", $options);
		$stories = $response["stories"] ?? [];
		$result = [];

		// @todo paginate here
		foreach ($stories as $entry)
		{
			$result[] = new FolderData($entry);
		}

		return $result;
	}

	/**
	 * Returns the ids of all registered components
	 */
	public function fetchAllRegisteredComponents () : array
	{
		return $this->getComponentIdMap()->getAllComponentKeys();
	}


	/**
	 *
	 */
	private function getComponentIdMap () : ComponentIdMap
	{
		if (null === $this->componentIdMap)
		{
			$this->componentIdMap = $this->fetchFreshComponentIdMap();
		}

		return $this->componentIdMap;
	}

	/**
	 * Fetches the component ID mapping
	 */
	private function fetchFreshComponentIdMap () : ComponentIdMap
	{
		try
		{
			// ensure that we stay in the rate limit
			$this->rateLimiter->consume()->wait();

			$response = $this->client->request(
				"GET",
				"components",
				$this->generateBaseOptions()->toArray(),
			);

			return new ComponentIdMap($response->toArray());
		}
		catch (ExceptionInterface $e)
		{
			throw new ApiRequestFailedException(\sprintf(
				"Failed to fetch existing components: %s",
				$e->getMessage(),
			), previous: $e);
		}
	}

	/**
	 */
	public function syncDatasourceEntries (
		string $datasourceSlug,
		array $updatedValues,
		?TorrStyle $io = null,
	) : void
	{
		$io?->writeln(\sprintf("• Fetching the id for datasource <fg=blue>%s</>", $datasourceSlug));

		$datasourceId = $this->getDatasourceId($datasourceSlug);
		$io?->writeln(\sprintf("• Found id <fg=yellow>%d</>", $datasourceId));

		$nameMap = [];
		$valueMap = [];


		$io?->writeln("• Fetching datasource entries...");

		foreach ($this->fetchDatasourceEntries($datasourceSlug) as $entry)
		{
			$nameMap[$entry["name"]] = $entry;
			$valueMap[$entry["value"]] = $entry;
		}

		$toAdd = [];
		$toUpdate = [];

		foreach ($updatedValues as $value => $name)
		{
			// if existing entry
			if (\array_key_exists($value, $valueMap))
			{
				if ($valueMap[$value]["name"] === $name)
				{
					continue;
				}

				$toUpdate[] = \array_replace($valueMap[$value], [
					"name" => $name,
				]);
				continue;
			}

			// if new entry
			if (\array_key_exists($name, $nameMap))
			{
				throw new DatasourceSyncFailedException(\sprintf(
					"Duplicate datasource name for name '%s' found, one new with key '%s' and existing '%s'.",
					$name,
					$value,
					$nameMap[$name]["value"],
				));
			}

			$toAdd[] = [
				"name" => $name,
				"value" => $value,
				"datasource_id" => $datasourceId,
			];
		}


		$io?->writeln(\sprintf("• Found <fg=blue>%d</> entries to add", \count($toAdd)));

		foreach ($toAdd as $entry)
		{
			$io?->writeln(\sprintf("• Adding <fg=yellow>%s</>", $entry["name"]));
			$this->sendRequest(
				"datasource_entries",
				options: (new HttpOptions())
					->setJson([
						"datasource_entry" => $entry,
					]),
				method: "POST",
			);
		}

		$io?->writeln(\sprintf("• Found <fg=blue>%d</> entries to update", \count($toUpdate)));

		foreach ($toUpdate as $entry)
		{
			$io?->writeln(\sprintf("• Updating <fg=yellow>%s</>", $entry["name"]));
			$this->sendRequest(
				"datasource_entries/{$entry["id"]}",
				options: (new HttpOptions())
					->setJson([
						"datasource_entry" => $entry,
					]),
				method: "PUT",
			);
		}

		$io?->writeln("-> <fg=green>done</>");
	}


	/**
	 * Fetches all datasource entries
	 *
	 * @return array<array{"id": int, "name": string, "value": string}>
	 */
	public function fetchDatasourceEntries (
		string $datasourceSlug,
	) : array
	{
		$options = (new HttpOptions())
			->setQuery([
				"datasource_slug" => $datasourceSlug,
			]);

		$result = $this->sendRequest("datasource_entries", $options);
		return $result["datasource_entries"];
	}

	/**
	 *
	 */
	private function getDatasourceId (
		string $datasourceSlug,
	) : int
	{
		$entries = $this->sendRequest("datasources");

		foreach ($entries["datasources"] ?? [] as $entry)
		{
			if ($entry["slug"] === $datasourceSlug)
			{
				return $entry["id"];
			}
		}

		throw new DatasourceSyncFailedException(\sprintf(
			"Could not find data source id for datasource '%s'",
			$datasourceSlug,
		));
	}


	/**
	 * Sends the request and returns the response
	 */
	private function sendRequest (
		string $path,
		HttpOptions $options = new HttpOptions(),
		string $method = "GET",
	) : array
	{
		try
		{
			// ensure that we stay in the rate limit
			$this->rateLimiter->consume()->wait();

			$formattedOptions = $options->toArray();
			$formattedOptions["headers"]["authorization"] = $this->config->getManagementToken();

			$response = $this->client->request(
				$method,
				$path,
				$formattedOptions,
			);

			return "" !== $response->getContent(true)
				? $response->toArray()
				: [];
		}
		catch (ExceptionInterface $exception)
		{
			$response = $exception instanceof HttpExceptionInterface
				? $exception->getResponse()
				: null;

			$this->logger->error("Failed management request {method} '{path}': {message}", [
				"method" => $method,
				"path" => $path,
				"message" => $exception->getMessage(),
				"statusCode" => $response?->getStatusCode(),
				// use unchanged, to not leak the token
				"options" => $options->toArray(),
				"response" => $response?->getContent(false),
			]);

			throw new ApiRequestFailedException(\sprintf(
				"Failed management request %s '%s': %s",
				$method,
				$path,
				$exception->getMessage(),
			), previous: $exception);
		}
	}

	/**
	 *
	 */
	private function generateBaseOptions () : HttpOptions
	{
		return (new HttpOptions())
			->setHeaders([
				"Authorization" => $this->config->getManagementToken(),
			]);
	}
}
