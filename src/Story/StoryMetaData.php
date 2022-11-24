<?php declare(strict_types=1);

namespace Torr\Storyblok\Story;

use Torr\Storyblok\Exception\Story\StoryHydrationFailed;

final class StoryMetaData
{
	private readonly array $data;

	/**
	 */
	public function __construct (
		array $data,
		/**
		 * The component type of the story's component
		 */
		private readonly string $type,
	)
	{
		unset($data["content"]);
		$this->data = $data;
	}

	/**
	 *
	 */
	public function getName () : string
	{
		return $this->data["name"];
	}

	/**
	 *
	 */
	public function getCreatedAt () : \DateTimeImmutable
	{
		return $this->parseDate($this->data["created_at"]);
	}

	/**
	 *
	 */
	public function getPublishedAt () : ?\DateTimeImmutable
	{
		return null !== $this->data["published_at"]
			? $this->parseDate($this->data["published_at"])
			: null;
	}

	/**
	 *
	 */
	public function getId () : int
	{
		return $this->data["id"];
	}

	/**
	 *
	 */
	public function getUuid () : string
	{
		return $this->data["uuid"];
	}

	/**
	 *
	 */
	public function getSlug () : string
	{
		return $this->data["slug"];
	}

	/**
	 *
	 */
	public function getFullSlug () : string
	{
		return $this->data["full_slug"];
	}

	/**
	 *
	 */
	public function isStartPage () : bool
	{
		return $this->data["is_startpage"];
	}

	/**
	 *
	 */
	public function getLocale () : string
	{
		return $this->data["lang"];
	}

	/**
	 */
	public function getType () : string
	{
		return $this->type;
	}


	/**
	 */
	private function parseDate (string $date) : \DateTimeImmutable
	{
		$parsed = \DateTimeImmutable::createFromFormat(\DateTimeInterface::RFC3339_EXTENDED, $date);

		if (false === $parsed)
		{
			throw new StoryHydrationFailed(\sprintf(
				"Could not parse date: %s",
				$date,
			));
		}

		return $parsed;
	}
}
