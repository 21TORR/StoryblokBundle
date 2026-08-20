<?php declare(strict_types=1);

namespace Torr\Storyblok\Folder;

final readonly class FolderData
{
	/**
	 */
	public function __construct (
		public int $id,
		public string $uuid,
		public string $name,
		public int $position,
		public string $slug,
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
