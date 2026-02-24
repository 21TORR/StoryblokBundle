<?php declare(strict_types=1);

namespace Torr\Storyblok\Backend;

use Torr\Storyblok\Story\StoryInterface;

final readonly class StoryblokBackendUrlGenerator
{
	/**
	 * Generates the URL to the edit screen for the given story
	 *
	 * @api
	 */
	public function generateStoryEditUrl (StoryInterface $story) : string
	{
		$metaData = $story->getMetaData();

		return $this->generateStoryEditUrlById(
			$metaData->getId(),
			$metaData->spaceId,
		);
	}

	/**
	 * Generates the URL to the edit screen for the given story id (the id, not the uuid)
	 *
	 * @api
	 */
	public function generateStoryEditUrlById (int $storyId, string $spaceId) : string
	{
		return \sprintf(
			"https://app.storyblok.com/#/me/spaces/%d/stories/0/0/%d",
			$spaceId,
			$storyId,
		);
	}
}
