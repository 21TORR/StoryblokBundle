<?php declare(strict_types=1);

namespace Torr\Storyblok\Story;

use Torr\Storyblok\Story\MetaData\DocumentMetaData;

/**
 * Base class for a standalone storyblok component
 */
abstract class StoryDocument extends StoryContent
{
	/**
	 */
	public function __construct (
		DocumentMetaData $metaData,
	)
	{
		parent::__construct($metaData);
	}


	/**
	 */
	public function getMetaData () : DocumentMetaData
	{
		\assert($this->metaData instanceof DocumentMetaData);
		return $this->metaData;
	}

	/**
	 */
	final public function getFullSlug () : string
	{
		return $this->getMetaData()->getFullSlug();
	}
}
