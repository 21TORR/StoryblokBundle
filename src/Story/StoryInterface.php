<?php declare(strict_types=1);

namespace Torr\Storyblok\Story;

interface StoryInterface
{
	/**
	 */
	public function getUuid () : string;

	/**
	 */
	public function getMetaData () : StoryMetaData;

	/**
	 */
	public function getFullSlug () : string;
}
