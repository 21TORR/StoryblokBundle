<?php declare(strict_types=1);

namespace Torr\Storyblok\Api\Data;

final readonly class StoryblokLink
{
	public int $id;
	public string $uuid;
	public string $slug;
	public string $name;
	public bool $isFolder;
	public int $position;

	/**
	 */
	public function __construct (array $data)
	{
		$this->id = $data["id"];
		$this->uuid = $data["uuid"];
		$this->slug = $data["slug"];
		$this->isFolder = $data["is_folder"];
		$this->position = $data["position"];
		$this->name = $data["name"];
	}

	/**
	 */
	public function getSlugSegments () : array
	{
		return \explode("/", \trim($this->slug, "/"));
	}
}
