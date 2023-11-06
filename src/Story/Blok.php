<?php declare(strict_types=1);

namespace Torr\Storyblok\Story;

use Torr\Storyblok\Story\MetaData\BlokMetaData;

abstract class Blok
{
	/**
	 */
	public function __construct (
		private readonly BlokMetaData $metaData,
	)
	{}


	/**
	 */
	final public function getMetaData () : BlokMetaData
	{
		return $this->metaData;
	}
}

