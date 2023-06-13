<?php declare(strict_types=1);

namespace Torr\Storyblok\Api\Transformer;

use Symfony\Contracts\Service\ResetInterface;
use Torr\Storyblok\Api\ContentApi;

final class StoryblokIdSlugMapper implements ResetInterface
{
	/** @var array<string|int, string>|null */
	private ?array $map = null;


	/**
	 */
	public function __construct (
		private readonly ContentApi $contentApi,
	) {}

	/**
	 */
	public function getFullSlugById (string|int $id) : ?string
	{
		return $this->getMap()[$id] ?? null;
	}


	/**
	 * @return array<string|int, string>
	 */
	private function getMap () : array
	{
		return $this->map ??= $this->fetchMap();
	}


	/**
	 * @return array<string|int, string>
	 */
	private function fetchMap () : array
	{
		$map = [];

		foreach ($this->contentApi->fetchAllStories("*") as $story)
		{
			// we have no overlap between the formats, so we can combine them into a single map
			$map[$story->getUuid()] = $story->getFullSlug();
			$map[$story->getMetaData()->getId()] = $story->getFullSlug();
		}

		return $map;
	}


	/**
	 * @inheritDoc
	 */
	public function reset () : void
	{
		$this->map = null;
	}
}
