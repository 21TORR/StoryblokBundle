<?php declare(strict_types=1);

namespace Torr\Storyblok\Api;

use Symfony\Component\HttpClient\HttpOptions;
use Symfony\Component\RateLimiter\LimiterInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Torr\Storyblok\Config\StoryblokConfig;
use Torr\Storyblok\Exception\Api\ContentRequestFailedException;
use Torr\Storyblok\Exception\Component\UnknownStoryTypeException;
use Torr\Storyblok\Exception\Story\InvalidDataException;
use Torr\Storyblok\Manager\ComponentManager;
use Torr\Storyblok\Release\ReleaseVersion;
use Torr\Storyblok\Story\Story;
use Torr\Storyblok\Story\StoryFactory;

final class ContentApi
{
	private const API_URL = "https://api.storyblok.com/v2/cdn/";
	private const STORYBLOK_UUID_REGEX = '/^[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{12}$/';
	private readonly HttpClientInterface $client;
	private readonly LimiterInterface $rateLimiter;

	/**
	 */
	public function __construct (
		HttpClientInterface $client,
		private readonly StoryblokConfig $config,
		private readonly StoryFactory $storyFactory,
		private readonly ComponentManager $componentManager,
		RateLimiterFactory $storyblokContentDeliveryLimiter,
	)
	{
		$this->rateLimiter = $storyblokContentDeliveryLimiter->create();
		$this->client = $client->withOptions(
			(new HttpOptions())
				->setBaseUri(self::API_URL)
				->toArray(),
		);
	}

	/**
	 * Fetches stories.
	 *
	 * This method provides certain commonly used named parameters, but also supports passing arbitrary parameters
	 * in the parameter. Passing named parameters will always overwrite parameters in $query.
	 *
	 * @throws ContentRequestFailedException
	 *
	 * @return Story[]
	 */
	private function fetchStoriesResultPage (
		array $query = [],
	) : array
	{
		// ensure that we stay in the rate limit
		$this->rateLimiter->consume()->wait();

		$query["token"] = $this->config->getContentToken();

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
			$stories = [];

			foreach ($data["stories"] as $storyData)
			{
				$stories[] = $this->storyFactory->createFromApiData($storyData);
			}

			// @todo return a paginated result here and automatically fetch all pages.
			return $stories;
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
	 * Loads a single story.
	 *
	 * @param string|int $identifier Can be the full slug, id or uuid
	 */
	public function fetchSingleStory (
		string|int $identifier,
		ReleaseVersion $version = ReleaseVersion::PUBLISHED,
	) : ?Story
	{
		// ensure that we stay in the rate limit
		$this->rateLimiter->consume()->wait();

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

		return $this->fetchStoriesResultPage($query);
	}
}
