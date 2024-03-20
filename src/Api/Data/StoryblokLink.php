<?php declare(strict_types=1);

namespace Torr\Storyblok\Api\Data;

final class StoryblokLink
{
	private int $id;
	private string $uuid;
	private string $slug;

	/**
	 */
	public function __construct (array $data)
	{
		$this->id = $data["id"];
		$this->uuid = $data["uuid"];
		$this->slug = $data["slug"];
	}

	/**
	 */
	public function getId () : int
	{
		return $this->id;
	}

	/**
	 */
	public function getUuid () : string
	{
		return $this->uuid;
	}

	/**
	 */
	public function getSlug () : string
	{
		return $this->slug;
	}
}
