<?php declare(strict_types=1);

namespace Torr\Storyblok\Story\Data;

use Symfony\Component\DependencyInjection\Attribute\Exclude;
use Torr\Storyblok\Story\Exception\InvalidStoryInitializationException;
use Torr\Storyblok\Story\MetaData\StoryMetaData;

/**
 */
#[Exclude]
abstract class Story
{
	public StoryMetaData $metaData
		{
		get => $this->metaData;
		set(StoryMetaData $metaData)
		{
			if (isset($this->metaData))
			{
				throw new InvalidStoryInitializationException("Can't initialize story multiple times");
			}

			$this->metaData = $metaData;
		}
	}
}
