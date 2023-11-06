<?php declare(strict_types=1);

namespace Torr\Storyblok\Story;

use Torr\Storyblok\Story\MetaData\DocumentMetaData;

abstract class Document
{
	/**
	 */
	public function __construct (
		private readonly DocumentMetaData $metaData,
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
	final public function getMetaData () : DocumentMetaData
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
