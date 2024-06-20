<?php declare(strict_types=1);

namespace Torr\Storyblok\Backend;

use Torr\Storyblok\Config\StoryblokConfig;
use Torr\Storyblok\Story\StoryInterface;

final readonly class StoryblokBackendUrlGenerator
{
	/**
	 */
	public function __construct (
		private StoryblokConfig $config,
	) {}

	/**
	 * Generates the URL to the edit screen for the given story
	 *
	 * @api
	 */
	public function generateStoryEditUrl (StoryInterface $story) : string
	{
		return $this->generateStoryEditUrlById($story->getMetaData()->getId());
	}

	/**
	 * Generates the URL to the edit screen for the given story id (the id, not the uuid)
	 *
	 * @api
	 */
	public function generateStoryEditUrlById (int $storyId) : string
	{
		return sprintf(
			"https://app.storyblok.com/#/me/spaces/%d/stories/0/0/%d",
			$this->config->getSpaceId(),
			$storyId,
		);
	}
}
