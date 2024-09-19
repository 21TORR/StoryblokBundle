<?php declare(strict_types=1);

namespace Torr\Storyblok\Api;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\HttpOptions;
use Symfony\Component\HttpClient\RetryableHttpClient;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Service\ResetInterface;
use Torr\Storyblok\Api\Data\PaginatedApiResult;
use Torr\Storyblok\Api\Data\SpaceInfo;
use Torr\Storyblok\Api\Data\StoryblokLink;
use Torr\Storyblok\Config\StoryblokConfig;
use Torr\Storyblok\Datasource\DatasourceEntry;
use Torr\Storyblok\Exception\Api\ContentRequestFailedException;
use Torr\Storyblok\Exception\Component\UnknownStoryTypeException;
use Torr\Storyblok\Exception\Config\InvalidConfigException;
use Torr\Storyblok\Exception\Story\InvalidDataException;
use Torr\Storyblok\Manager\ComponentManager;
use Torr\Storyblok\Release\ReleaseVersion;
use Torr\Storyblok\Story\Story;
use Torr\Storyblok\Story\StoryFactory;

final class ContentApi implements ResetInterface
{
	private const API_URL = "https://api.storyblok.com/v2/cdn/";
	private const STORYBLOK_UUID_REGEX = '/^[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{12}$/';
	private readonly HttpClientInterface $client;
	private ?SpaceInfo $spaceInfo = null;

	/**
	 */
	public function __construct (
		HttpClientInterface $client,
		private readonly StoryblokConfig $config,
		private readonly StoryFactory $storyFactory,
		private readonly ComponentManager $componentManager,
		private readonly LoggerInterface $logger,
	)
	{
		$this->client = new RetryableHttpClient(
			$client->withOptions(
				(new HttpOptions())
					->setBaseUri(self::API_URL)
					->toArray(),
			),
		);
	}

	/**
	 * Loads a single story.
	 *
	 * @param string|int $identifier Can be the full slug, id or uuid
	 */
	public function fetchSingleStory (
		string|int $identifier,
		ReleaseVersion $version = ReleaseVersion::PUBLISHED,
	) : ?Story
	{
		try
		{
			$identifier = ltrim((string) $identifier, "/");

			$queryParameters = [
				"token" => $this->config->getContentToken(),
				"version" => $version->value,
			];

			if (preg_match(self::STORYBLOK_UUID_REGEX, $identifier))
			{
				$queryParameters["find_by"] = "uuid";
			}

			$response = $this->client->request(
				"GET",
				"stories/{$identifier}",
				(new HttpOptions())
					->setQuery($queryParameters)
					->toArray(),
			);

			if (404 === $response->getStatusCode())
			{
				return null;
			}

			$data = $response->toArray();

			return $this->storyFactory->createFromApiData($data["story"]);
		}
		catch (ExceptionInterface $exception)
		{
			throw new ContentRequestFailedException(\sprintf(
				"Content request failed for single story '%s': %s",
				$identifier,
				$exception->getMessage(),
			), previous: $exception);
		}
	}

	/**
	 * Fetches all stories of a given type.
	 *
	 * This method provides certain commonly used named parameters, but also supports passing arbitrary parameters
	 * in the parameter. Passing named parameters will always overwrite parameters in $query.
	 *
	 * @template TStory of Story
	 *
	 * @param class-string<TStory> $storyType
	 *
	 * @return list<TStory>
	 *
	 * @throws ContentRequestFailedException
	 * @throws UnknownStoryTypeException
	 */
	public function fetchStories (
		string $storyType,
		string $slug,
		?string $locale = null,
		array $query = [],
		ReleaseVersion $version = ReleaseVersion::PUBLISHED,
	) : array
	{
		$component = $this->componentManager->getComponentByStoryType($storyType);

		$query["content_type"] = $component::getKey();
		$result = $this->fetchAllStories(
			slug: $slug,
			locale: $locale,
			query: $query,
			version: $version,
		);

		foreach ($result as $story)
		{
			if (!is_a($story, $storyType))
			{
				throw new InvalidDataException(\sprintf(
					"Requested stories for type '%s', but encountered story of type '%s'.",
					$storyType,
					$story::class,
				));
			}
		}

		/** @var list<TStory> $result */
		return $result;
	}

	/**
	 * Fetches all stories (regardless of type).
	 *
	 * This method provides certain commonly used named parameters, but also supports passing arbitrary parameters
	 * in the parameter. Passing named parameters will always overwrite parameters in $query.
	 *
	 * @param string|string[]|null $slug
	 *
	 * @return list<Story>
	 *
	 * @throws ContentRequestFailedException
	 */
	public function fetchAllStories (
		string|array|null $slug,
		?string $locale = null,
		array $query = [],
		ReleaseVersion $version = ReleaseVersion::PUBLISHED,
	) : array
	{
		// force per_page to the maximum to minimize pagination
		$query["per_page"] = 100;
		$query["version"] = $version->value;
		$query["cv"] = $this->getSpaceInfo()->getCacheVersion();
		$query["sort_by"] ??= "position:asc";

		if (null !== $slug)
		{
			$query["by_slugs"] = \is_array($slug)
				? implode(",", $slug)
				: $slug;
		}

		if (null !== $locale)
		{
			$query["language"] = $locale;
		}

		$result = [];
		$page = 1;

		do
		{
			$currentPage = $this->fetchStoriesResultPage($query, $page);

			foreach ($currentPage->entries as $story)
			{
				$result[] = $story;
			}

			++$page;
		}
		while ($currentPage->totalPages >= $page);

		return $result;
	}

	/**
	 * @throws InvalidConfigException
	 * @throws ContentRequestFailedException
	 */
	public function getSpaceInfo () : SpaceInfo
	{
		if (null !== $this->spaceInfo)
		{
			return $this->spaceInfo;
		}

		try
		{
			$response = $this->client->request(
				"GET",
				"spaces/me/",
				(new HttpOptions())
					->setQuery([
						"token" => $this->config->getContentToken(),
					])
					->toArray(),
			);

			$data = $response->toArray();
			$spaceInfo = new SpaceInfo($data["space"]);

			// This check is important, as the content token is tied to the space, so you don't need the space id
			// for any content API requests. However, the management API is using the space id from the config.
			// If you have a misconfiguration, you could send the management API requests and the content API requests
			// to different spaces.
			if ($spaceInfo->getId() !== $this->config->getSpaceId())
			{
				$this->logger->critical("Invalid storyblok config: configured space id is {configuredSpaceId}, but content token belongs to space {tokenSpaceId} ({name})", [
					"configuredSpaceId" => $this->config->getSpaceId(),
					"tokenSpaceId" => $spaceInfo->getId(),
					"name" => $spaceInfo->getName(),
				]);

				throw new InvalidConfigException(\sprintf(
					"Invalid storyblok config: configured space id is '%s', but content token belongs to space id '%s' (name '%s')",
					$this->config->getSpaceId(),
					$spaceInfo->getId(),
					$spaceInfo->getName(),
				));
			}

			return $this->spaceInfo = new SpaceInfo($data["space"]);
		}
		catch (ExceptionInterface $exception)
		{
			throw new ContentRequestFailedException(\sprintf(
				"Failed to fetch space info: %s",
				$exception->getMessage(),
			), previous: $exception);
		}
	}

	/**
	 * Fetches stories.
	 *
	 * This method provides certain commonly used named parameters, but also supports passing arbitrary parameters
	 * in the parameter. Passing named parameters will always overwrite parameters in $query.
	 *
	 * @return PaginatedApiResult<Story>
	 *
	 * @throws ContentRequestFailedException
	 */
	private function fetchStoriesResultPage (
		array $query = [],
		int $page = 1,
	) : PaginatedApiResult
	{
		$query["token"] = $this->config->getContentToken();
		$query["cv"] = $this->getSpaceInfo()->getCacheVersion();
		$query["page"] = $page;

		// Prevent a redirect from the API by sorting all of our query parameters alphabetically first
		ksort($query);

		try
		{
			$response = $this->client->request(
				"GET",
				"stories",
				(new HttpOptions())
					->setQuery($query)
					->toArray(),
			);

			$data = $response->toArray();
			$headers = $response->getHeaders();
			$perPage = $this->parseHeaderAsInt($headers, "per-page");
			$totalNumberOfItems = $this->parseHeaderAsInt($headers, "total");

			$stories = [];

			if (
				!\is_array($data["stories"])
				|| null === $perPage
				|| null === $totalNumberOfItems
				|| $perPage <= 0
			)
			{
				$this->logger->error("Content request failed: invalid response structure / missing headers", [
					"query" => $query,
					"headers" => $headers,
					"response" => $response->getContent(false),
					"perPage" => $perPage,
					"totalNumberOfItems" => $totalNumberOfItems,
				]);

				throw new ContentRequestFailedException("Content request failed: invalid response structure / missing headers");
			}

			foreach ($data["stories"] as $storyData)
			{
				$hydrated = $this->storyFactory->createFromApiData($storyData);

				if (null !== $hydrated)
				{
					$stories[] = $hydrated;
				}
			}

			return new PaginatedApiResult(
				perPage: $perPage,
				totalPages: (int) ceil($totalNumberOfItems / $perPage),
				entries: $stories,
			);
		}
		catch (ExceptionInterface $exception)
		{
			throw new ContentRequestFailedException(\sprintf(
				"Content request failed: %s",
				$exception->getMessage(),
			), previous: $exception);
		}
	}

	/**
	 * Gets the first header as int/null
	 *
	 * @param string[][] $headers
	 */
	private function parseHeaderAsInt (array $headers, string $headerName) : ?int
	{
		$value = $headers[$headerName][0] ?? null;

		return ctype_digit($value)
			? (int) $value
			: null;
	}

	/**
	 * Fetches all entry for the given datasource.
	 *
	 * @param string|string[]|null $slug the slug of the datasource
	 *
	 * @return array<array-key, DatasourceEntry> DatasourceEntry value to DatasourceEntry
	 *
	 * @throws ContentRequestFailedException
	 */
	public function fetchDatasourceEntries (
		string|array|null $slug,
		?string $dimension = null,
		ReleaseVersion $version = ReleaseVersion::PUBLISHED,
	) : array
	{
		$query = [
			"datasource" => $slug,
			"per_page" => 100,
			"version" => $version->value,
		];

		if (null !== $dimension)
		{
			$query["dimension"] = $dimension;
		}

		$result = [];
		$page = 1;

		do
		{
			$currentPage = $this->fetchDatasourceEntriesResultPage($query, $page);

			foreach ($currentPage->entries as $value => $datasourceEntry)
			{
				$result[$value] = $datasourceEntry;
			}

			++$page;
		}
		while ($currentPage->totalPages >= $page);

		return $result;
	}

	/**
	 * Fetches datasource entries.
	 *
	 * @return PaginatedApiResult<DatasourceEntry>
	 *
	 * @throws ContentRequestFailedException
	 */
	private function fetchDatasourceEntriesResultPage (
		array $query = [],
		int $page = 1,
	) : PaginatedApiResult
	{
		$query["token"] = $this->config->getContentToken();
		$query["cv"] = $this->getSpaceInfo()->getCacheVersion();
		$query["page"] = $page;

		// Prevent a redirect from the API by sorting all of our query parameters alphabetically first
		ksort($query);

		try
		{
			$response = $this->client->request(
				"GET",
				"datasource_entries",
				(new HttpOptions())
					->setQuery($query)
					->toArray(),
			);

			$data = $response->toArray();
			$headers = $response->getHeaders();
			$perPage = $this->parseHeaderAsInt($headers, "per-page");
			$totalNumberOfItems = $this->parseHeaderAsInt($headers, "total");

			$entries = [];

			if (
				!\is_array($data["datasource_entries"])
				|| null === $perPage
				|| null === $totalNumberOfItems
				|| $perPage <= 0
			)
			{
				$this->logger->error("Content request failed: invalid response structure / missing headers", [
					"query" => $query,
					"headers" => $headers,
					"response" => $response->getContent(false),
					"perPage" => $perPage,
					"totalNumberOfItems" => $totalNumberOfItems,
				]);

				throw new ContentRequestFailedException("Content request failed: invalid response structure / missing headers");
			}

			foreach ($data["datasource_entries"] as $entryData)
			{
				$entry = new DatasourceEntry(
					$entryData["name"],
					$entryData["dimension_value"] ?? $entryData["value"],
				);

				$entries[$entry->value] = $entry;
			}

			return new PaginatedApiResult(
				perPage: $perPage,
				totalPages: (int) ceil($totalNumberOfItems / $perPage),
				entries: $entries,
			);
		}
		catch (ExceptionInterface $exception)
		{
			throw new ContentRequestFailedException(\sprintf(
				"Content request failed: %s",
				$exception->getMessage(),
			), previous: $exception);
		}
	}

	/**
	 * Fetches all links.
	 *
	 * @return list<StoryblokLink>
	 *
	 * @throws ContentRequestFailedException
	 */
	public function fetchAllLinks (
		ReleaseVersion $version = ReleaseVersion::PUBLISHED,
	) : array
	{
		$query = [
			// force per_page to the maximum to minimize pagination
			"per_page" => 1000,
			"version" => $version->value,
		];

		$result = [];
		$page = 1;

		do
		{
			$currentPage = $this->fetchLinksResultPage($query, $page);

			foreach ($currentPage->entries as $link)
			{
				$result[] = $link;
			}

			++$page;
		}
		while ($currentPage->totalPages >= $page);

		return $result;
	}

	/**
	 * Fetches links.
	 *
	 * @return PaginatedApiResult<StoryblokLink>
	 *
	 * @throws ContentRequestFailedException
	 */
	private function fetchLinksResultPage (
		array $query = [],
		int $page = 1,
	) : PaginatedApiResult
	{
		$query["token"] = $this->config->getContentToken();
		$query["cv"] = $this->getSpaceInfo()->getCacheVersion();
		$query["page"] = $page;
		$query["paginated"] = 1;

		// Prevent a redirect from the API by sorting all of our query parameters alphabetically first
		ksort($query);

		try
		{
			$response = $this->client->request(
				"GET",
				"links",
				(new HttpOptions())
					->setQuery($query)
					->toArray(),
			);

			$data = $response->toArray();
			$headers = $response->getHeaders();
			$perPage = $this->parseHeaderAsInt($headers, "per-page");
			$totalNumberOfItems = $this->parseHeaderAsInt($headers, "total");

			$links = [];

			if (
				!\is_array($data["links"])
				|| null === $perPage
				|| null === $totalNumberOfItems
				|| $perPage <= 0
			)
			{
				$this->logger->error("Content request failed: invalid response structure / missing headers", [
					"query" => $query,
					"headers" => $headers,
					"response" => $response->getContent(false),
					"perPage" => $perPage,
					"totalNumberOfItems" => $totalNumberOfItems,
				]);

				throw new ContentRequestFailedException("Content request failed: invalid response structure / missing headers");
			}

			foreach ($data["links"] as $linkData)
			{
				$links[] = new StoryblokLink($linkData);
			}

			return new PaginatedApiResult(
				perPage: $perPage,
				totalPages: (int) ceil($totalNumberOfItems / $perPage),
				entries: $links,
			);
		}
		catch (ExceptionInterface $exception)
		{
			throw new ContentRequestFailedException(\sprintf(
				"Content request failed: %s",
				$exception->getMessage(),
			), previous: $exception);
		}
	}

	/**
	 * Resets the service
	 */
	public function reset () : void
	{
		$this->spaceInfo = null;
	}
}
