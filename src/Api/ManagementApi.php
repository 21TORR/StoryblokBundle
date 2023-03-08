<?php declare(strict_types=1);

namespace Torr\Storyblok\Api;

use Symfony\Component\HttpClient\HttpOptions;
use Symfony\Component\RateLimiter\LimiterInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Torr\Storyblok\Api\Data\ApiActionPerformed;
use Torr\Storyblok\Api\Data\ComponentIdMap;
use Torr\Storyblok\Api\Data\FolderMetaData;
use Torr\Storyblok\Config\StoryblokConfig;
use Torr\Storyblok\Exception\Api\ApiRequestFailedException;

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
	)
	{
		$this->rateLimiter = $storyblokManagementLimiter->create();
		$this->client = $client->withOptions(
			(new HttpOptions())
				->setBaseUri(\sprintf(self::API_URL, $this->config->getSpaceId()))
				->toArray(),
		);
	}

	/**
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
	 * @return array<string, FolderMetaData> Map of local url to folder meta data
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
				"sort_by" => "position:asc",
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

				$map[$localSlug] = new FolderMetaData(
					$localSlug,
					$entry["name"],
					$entry["position"],
				);
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
