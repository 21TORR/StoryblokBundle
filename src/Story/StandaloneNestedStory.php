<?php declare(strict_types=1);

namespace Torr\Storyblok\Story;

use Torr\Storyblok\Story\MetaData\StandaloneStoryMetaData;

/**
 * Base class for a standalone storyblok component
 */
abstract class StandaloneNestedStory extends NestedStory
{
	/**
	 */
	public function __construct (
		StandaloneStoryMetaData $metaData,
	)
	{
		parent::__construct($metaData);
	}


	/**
	 */
	public function getMetaData () : StandaloneStoryMetaData
	{
		\assert($this->metaData instanceof StandaloneStoryMetaData);
		return $this->metaData;
	}

	/**
	 */
	final public function getFullSlug () : string
	{
		return $this->getMetaData()->getFullSlug();
	}
}
