<?php declare(strict_types=1);

namespace Torr\Storyblok\Folder;

final class FolderData
{
	/**
	 */
	public function __construct (
		private readonly array $data,
	) {}

	/**
	 */
	public function getName () : string
	{
		return $this->data["name"];
	}

	/**
	 */
	public function getPosition () : int
	{
		return $this->data["position"];
	}

	/**
	 */
	public function getFullSlug () : string
	{
		return $this->data["full_slug"];
	}

	/**
	 */
	public function getSlugSegments () : array
	{
		return \explode("/", \trim($this->getFullSlug(), "/"));
	}
}
