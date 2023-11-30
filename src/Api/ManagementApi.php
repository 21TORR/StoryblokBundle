<?php declare(strict_types=1);

namespace Torr\Storyblok\Api;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\HttpOptions;
use Symfony\Component\HttpClient\RetryableHttpClient;
use Symfony\Component\RateLimiter\LimiterInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Torr\Storyblok\Api\Data\ApiActionPerformed;
use Torr\Storyblok\Api\Data\ComponentIdMap;
use Torr\Storyblok\Config\StoryblokConfig;
use Torr\Storyblok\Exception\Api\ApiRequestFailedException;
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

		try
		{
			// ensure that we stay in the rate limit
			$this->rateLimiter->consume()->wait();

			$config["component_group_uuid"] = $this->getOrCreatedComponentGroupUuid($componentGroupLabel);

			$options = $this->generateBaseOptions()
				->setJson([
					"component" => $config,
				])
				->toArray();

			$componentId = $this->getComponentIdMap()->getComponentId($config["name"]);

			$response = null !== $componentId
				? $this->client->request("PUT", "components/{$componentId}", $options)
				: $this->client->request("POST", "components", $options);

			// add id to component id map
			$data = $response->toArray();
			$componentIdMap->registerComponent($data["component"]["name"], $data["component"]["id"]);

			return null !== $componentId
				? ApiActionPerformed::UPDATED
				: ApiActionPerformed::ADDED;
		}
		catch (ExceptionInterface $e)
		{
			throw new ApiRequestFailedException(\sprintf(
				"Management API request failed: %s",
				$e->getMessage(),
			), previous: $e);
		}
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

		try
		{
			// ensure that we stay in the rate limit
			$this->rateLimiter->consume()->wait();

			$response = $this->client->request(
				"POST",
				"component_groups",
				$this->generateBaseOptions()
					->setJson([
						"component_group" => [
							"name" => $name,
						],
					])
					->toArray(),
			);

			$data = $response->toArray();
			$uuid = $data["component_group"]["uuid"];
			$idMap->registerComponentGroup($name, $uuid);

			return $uuid;
		}
		catch (ExceptionInterface $e)
		{
			throw new ApiRequestFailedException(\sprintf(
				"Failed to fetch create component group '%s': %s",
				$name,
				$e->getMessage(),
			), previous: $e);
		}
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

		$options = $this->generateBaseOptions()
			->setQuery([
				"folder_only" => true,
				"starts_with" => $slugPrefix,
				"per_page" => 100,
			]);

		try
		{
			$map = [];
			$replacement = "~" . \preg_quote($slugPrefix, "~") . "~";

			$response = $this->client->request("GET", "stories", $options->toArray());
			$stories = $response->toArray()["stories"] ?? [];

			// @todo paginate here
			foreach ($stories as $entry)
			{
				// use heading slash to local url
				$localSlug = "/" . \preg_replace($replacement, "", $entry["full_slug"]);
				$map[$localSlug] = $entry["name"];
			}

			return $map;
		}
		catch (ExceptionInterface $e)
		{
			throw new ApiRequestFailedException(\sprintf(
				"Failed to fetch folder title structure: %s",
				$e->getMessage(),
			), previous: $e);
		}
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

		$options = $this->generateBaseOptions()
			->setQuery([
				"folder_only" => true,
				"starts_with" => $slugPrefix,
				"per_page" => 100,
			]);

		try
		{
			$response = $this->client->request("GET", "stories", $options->toArray());
			$stories = $response->toArray()["stories"] ?? [];
			$result = [];

			// @todo paginate here
			foreach ($stories as $entry)
			{
				$result[] = new FolderData($entry);
			}

			return $result;
		}
		catch (ExceptionInterface $e)
		{
			throw new ApiRequestFailedException(\sprintf(
				"Failed to fetch folder title structure: %s",
				$e->getMessage(),
			), previous: $e);
		}
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
	 * @param array<string, string> The values to add/updated. Format: value => name
	 */
	public function syncDatasourceEntries (
		string $datasourceSlug,
		array $updatedValues
	)
	{
		$nameMap = [];
		$valueMap = [];

		foreach ($this->fetchDatasourceEntries($datasourceSlug) as $entry)
		{
			$nameMap[$entry["name"]] = $entry;
			$valueMap[$entry["value"]] = $entry;
		}

		$toAdd = [];
		$toUpdated = [];

		foreach ($updatedValues as $value => $name)
		{
			// if existing entry
			if (\array_key_exists($value, $valueMap))
			{
				if ($valueMap[$value]["name"] === $name)
				{
					continue;
				}

				$toUpdated[] = \array_replace($valueMap[$value], [
					"name" => $name,
				]);
				continue;
			}

			// if new entry
			if (\array_key_exists($name, $nameMap))
			{
				throw new
			}
		}


		dd($this->fetchDatasourceEntries($datasourceSlug));
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

			return $response->toArray();
		}
		catch (ExceptionInterface $e)
		{
			throw new ApiRequestFailedException(\sprintf(
				"Failed management request %s '%s': %s",
				$method,
				$path,
				$e->getMessage(),
			), previous: $e);
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
