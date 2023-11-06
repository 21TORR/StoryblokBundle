<?php declare(strict_types=1);

namespace Torr\Storyblok\Story;

use Torr\Storyblok\Story\MetaData\ContentMetaData;

/**
 * Base class for a nestable content element
 */
abstract class StoryContent
{
	/**
	 */
	public function __construct (
		protected readonly ContentMetaData $metaData,
	)
	{}


	/**
	 */
	public function getUuid () : string
	{
		return $this->metaData->getUuid();
	}


	/**
	 */
	public function getMetaData () : ContentMetaData
	{
		return $this->metaData;
	}
}

