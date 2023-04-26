<?php declare(strict_types=1);

namespace Torr\Storyblok\Api;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\HttpOptions;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Service\ResetInterface;
use Torr\Storyblok\Api\Data\PaginatedApiResult;
use Torr\Storyblok\Config\StoryblokConfig;
use Torr\Storyblok\Exception\Api\ContentRequestFailedException;
use Torr\Storyblok\Exception\Component\UnknownStoryTypeException;
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
	private ?int $cacheVersion = null;
	/** @var array<string, string> */
	private array $uuidToSlugCache = [];

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
		$this->client = $client->withOptions(
			(new HttpOptions())
				->setBaseUri(self::API_URL)
				->toArray(),
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
			$identifier = \ltrim((string) $identifier, "/");

			$queryParameters = [
				"token" => $this->config->getContentToken(),
				"version" => $version->value,
			];

			if (\preg_match(self::STORYBLOK_UUID_REGEX, $identifier))
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
	 * @throws ContentRequestFailedException
	 * @throws UnknownStoryTypeException
	 *
	 * @return array<TStory>
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
			if (!\is_a($story, $storyType))
			{
				throw new InvalidDataException(\sprintf(
					"Requested stories for type '%s', but encountered story of type '%s'.",
					$storyType,
					\get_class($story),
				));
			}
		}

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
	 * @throws ContentRequestFailedException
	 *
	 * @return array<Story>
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
		$query["cv"] = $this->getCacheVersion();
		$query["sort_by"] ??= "position:asc";

		if (null !== $slug)
		{
			$query["by_slugs"] = \is_array($slug)
				? \implode(",", $slug)
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

			foreach ($currentPage->stories as $story)
			{
				$result[] = $story;
			}

			++$page;
		}
		while ($currentPage->totalPages >= $page);

		return $result;
	}

	/**
	 * Fetches the full slug of the story by its id
	 */
	public function fetchFullSlugByUuid (string|int $identifier) : string|null
	{
		if (isset($this->uuidToSlugCache[$identifier]))
		{
			return $this->uuidToSlugCache[$identifier];
		}

		$story = $this->fetchSingleStory($identifier, ReleaseVersion::DRAFT);

		if (null === $story)
		{
			return null;
		}

		$this->uuidToSlugCache[$story->getUuid()] = $story->getFullSlug();
		$this->uuidToSlugCache[$story->getMetaData()->getId()] = $story->getFullSlug();
		return $story->getFullSlug();
	}

	/**
	 * Returns the storyblok-internal cache version, which is used to increase the
	 * cache rate in the following requests.
	 */
	private function getCacheVersion () : int
	{
		if (null !== $this->cacheVersion)
		{
			return $this->cacheVersion;
		}

		try
		{
			$response = $this->client->request(
				"GET",
				"stories",
				(new HttpOptions())
					->setQuery([
						"per_page" => 1,
						"token" => $this->config->getContentToken(),
					])
					->toArray(),
			);

			$data = $response->toArray();
			return $this->cacheVersion = $data["cv"];
		}
		catch (ExceptionInterface $exception)
		{
			throw new ContentRequestFailedException(\sprintf(
				"Failed to fetch cache version: %s",
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
	 * @throws ContentRequestFailedException
	 */
	private function fetchStoriesResultPage (
		array $query = [],
		int $page = 1,
		int $remainingRetries = 3,
	) : PaginatedApiResult
	{
		$query["token"] = $this->config->getContentToken();
		$query["cv"] = $this->getCacheVersion();
		$query["page"] = $page;

		// Prevent a redirect from the API by sorting all of our query parameters alphabetically first
		\ksort($query);

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
				$stories[] = $this->storyFactory->createFromApiData($storyData);
			}

			return new PaginatedApiResult(
				perPage: $perPage,
				totalPages: (int) \ceil($totalNumberOfItems / $perPage),
				stories: $stories,
			);
		}
		catch (ExceptionInterface $exception)
		{
			// reduce number of remaining retries
			--$remainingRetries;

			if (
				$remainingRetries > 0
				&& $exception instanceof HttpExceptionInterface
				&& 429 === $exception->getResponse()->getStatusCode()
			)
			{
				$this->logger->debug("Encountered rate limit error, retrying", [
					"query" => $query,
				]);

				// wait for 100ms before retrying
				\usleep(100_000);

				// retry
				return $this->fetchStoriesResultPage(
					$query,
					$page,
					$remainingRetries,
				);
			}

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

		return \ctype_digit($value)
			? (int) $value
			: null;
	}

	/**
	 * Resets the service
	 */
	public function reset () : void
	{
		$this->cacheVersion = null;
		$this->uuidToSlugCache = [];
	}
}
