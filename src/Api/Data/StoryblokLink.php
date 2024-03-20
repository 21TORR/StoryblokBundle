<?php declare(strict_types=1);

namespace Torr\Storyblok\Api\Data;

final readonly class StoryblokLink
{
	public int $id;
	public string $uuid;
	public string $slug;

	/**
	 */
	public function __construct (array $data)
	{
		$this->id = $data["id"];
		$this->uuid = $data["uuid"];
		$this->slug = $data["slug"];
	}
}
