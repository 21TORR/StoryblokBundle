<?php declare(strict_types=1);

namespace Torr\Storyblok\Story\Data;

use Symfony\Component\DependencyInjection\Attribute\Exclude;
use Torr\Storyblok\Story\Exception\InvalidStoryInitializationException;
use Torr\Storyblok\Story\MetaData\BlokMetaData;

/**
 * @final
 */
#[Exclude]
abstract class BlokStory
{
	public BlokMetaData $metaData
		{
			get => $this->metaData;
			set (BlokMetaData $metaData)
			{
				if (isset($this->metaData))
				{
					throw new InvalidStoryInitializationException("Can't initialize blok multiple times");
				}

				$this->metaData = $metaData;
			}
		}
}
