<?php declare(strict_types=1);

namespace Torr\Storyblok\Story\Data;

use Symfony\Component\DependencyInjection\Attribute\Exclude;
use Torr\Storyblok\Story\Exception\InvalidStoryInitializationException;
use Torr\Storyblok\Story\MetaData\DocumentMetaData;

/**
 *
 */
#[Exclude]
abstract class DocumentStory
{
	public DocumentMetaData $metaData
	{
		get => $this->metaData;
		set (DocumentMetaData $metaData)
		{
			if (isset($this->metaData))
			{
				throw new InvalidStoryInitializationException("Can't initialize document multiple times");
			}

			$this->metaData = $metaData;
		}
	}
}
