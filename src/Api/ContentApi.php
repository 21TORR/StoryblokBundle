<?php declare(strict_types=1);

namespace Torr\Storyblok\Api;

use Symfony\Component\HttpClient\HttpOptions;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Torr\Storyblok\Config\StoryblokConfig;
use Torr\Storyblok\Exception\Api\ContentRequestFailedException;
use Torr\Storyblok\Story\Story;
use Torr\Storyblok\Story\StoryFactory;

final class ContentApi
{
	private const API_URL = "https://api.storyblok.com/v2/cdn/";
	private readonly HttpClientInterface $client;

	/**
	 */
	public function __construct (
		private readonly StoryblokConfig $config,
		HttpClientInterface $client,
		private readonly StoryFactory $storyFactory,
	)
	{
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
	private function fetchStories (
		?string $language,
		?string $type = null,
		array $query = [],
	) : array
	{
		if (null !== $type)
		{
			$query["content_type"] = $type;
		}

		if (null !== $language)
		{
			$query["language"] = $language;
		}

		$query["token"] = $this->config->getContentToken();

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
	 * Fetches all stories and automatically resolves pagination.
	 *
	 * This method provides certain commonly used named parameters, but also supports passing arbitrary parameters
	 * in the parameter. Passing named parameters will always overwrite parameters in $query.
	 *
	 * @throws ContentRequestFailedException
	 *
	 * @return Story[]
	 */
	public function fetchAllStories (
		?string $language,
		?string $type = null,
		array $query = [],
	) : array
	{
		// force per_page to the maximum to minimize pagination
		$query["per_page"] = 100;

		return $this->fetchStories($language, $type, $query);
	}
}
