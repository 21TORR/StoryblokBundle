<?php declare(strict_types=1);

namespace Torr\Storyblok\Story;

abstract class Story
{
	/**
	 */
	public function __construct (
		private readonly StoryMetaData $metaData,
	)
	{}

	/**
	 */
	final public function getUuid () : string
	{
		return $this->metaData->getUuid();
	}

	/**
	 */
	final public function getMetaData () : StoryMetaData
	{
		return $this->metaData;
	}

	/**
	 */
	final public function getFullSlug () : string
	{
		return $this->metaData->getFullSlug();
	}
}
