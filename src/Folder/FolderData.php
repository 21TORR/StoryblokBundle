<?php declare(strict_types=1);

namespace Torr\Storyblok\Folder;

final readonly class FolderData
{
	/**
	 */
	public function __construct (
		private string $name,
		private int $position,
		private string $slug,
	) {}

	/**
	 */
	public function getName () : string
	{
		return $this->name;
	}

	/**
	 */
	public function getPosition () : int
	{
		return $this->position;
	}

	/**
	 */
	public function getFullSlug () : string
	{
		return $this->slug;
	}

	/**
	 */
	public function getSlugSegments () : array
	{
		return explode("/", trim($this->slug, "/"));
	}
}
