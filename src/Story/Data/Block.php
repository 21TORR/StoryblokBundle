<?php declare(strict_types=1);

namespace Torr\Storyblok\Story\Data;

use Symfony\Component\DependencyInjection\Attribute\Exclude;
use Torr\Storyblok\Story\Exception\InvalidStoryInitializationException;
use Torr\Storyblok\Story\MetaData\BlockMetaData;

/**
 * @final
 */
#[Exclude]
abstract class Block
{
	public BlockMetaData $metaData
		{
		get => $this->metaData;
		set(BlockMetaData $metaData)
		{
			if (isset($this->metaData))
			{
				throw new InvalidStoryInitializationException("Can't initialize block multiple times");
			}

			$this->metaData = $metaData;
		}
	}
}
