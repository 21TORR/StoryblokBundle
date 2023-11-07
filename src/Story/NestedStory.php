<?php declare(strict_types=1);

namespace Torr\Storyblok\Story;

use Torr\Storyblok\Story\MetaData\NestedStoryMetaData;

/**
 * Base class for a nestable content element
 */
abstract class NestedStory
{
	/**
	 */
	public function __construct (
		protected readonly NestedStoryMetaData $metaData,
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
	public function getMetaData () : NestedStoryMetaData
	{
		return $this->metaData;
	}
}

