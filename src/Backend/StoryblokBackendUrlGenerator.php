<?php declare(strict_types=1);

namespace Torr\Storyblok\Backend;

use Torr\Storyblok\Config\StoryblokConfig;
use Torr\Storyblok\Story\StoryInterface;

final class StoryblokBackendUrlGenerator
{
	/**
	 */
	public function __construct (
		private readonly StoryblokConfig $config,
	) {}

	/**
	 * Generates the URL to the edit screen for the given story
	 */
	public function generateStoryEditUrl (StoryInterface $story) : string
	{
		return \sprintf(
			"https://app.storyblok.com/#/me/spaces/%d/stories/0/0/%d",
			$this->config->getSpaceId(),
			$story->getMetaData()->getId(),
		);
	}
}
