<?php declare(strict_types=1);

namespace Torr\Storyblok\Story\Data;

use Symfony\Component\DependencyInjection\Attribute\Exclude;
use Torr\Storyblok\Story\Exception\InvalidStoryInitializationException;
use Torr\Storyblok\Story\MetaData\NestedStoryMetaData;

/**
 * @final
 */
#[Exclude]
abstract class NestedStory
{
	public NestedStoryMetaData $metaData
		{
		get => $this->metaData;
		set(NestedStoryMetaData $metaData)
		{
			if (isset($this->metaData))
			{
				throw new InvalidStoryInitializationException("Can't initialize story multiple times");
			}

			$this->metaData = $metaData;
		}
	}
}
