<?php declare(strict_types=1);

namespace Torr\Storyblok\Story;

use Torr\Storyblok\Exception\Story\StoryHydrationFailed;

final class StoryMetaData
{
	private readonly array $data;
	private readonly array $slugSegments;

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
		$this->slugSegments = \explode("/", \rtrim($data["full_slug"], "/"));
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
		return $this->slugSegments[\count($this->slugSegments) - 1];
	}

	/**
	 * The full slug of the story
	 */
	public function getFullSlug () : string
	{
		return \implode("/", $this->slugSegments);
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
	 * Returns the slug of the parent
	 * (without trailing slash).
	 */
	public function getParentSlug () : ?string
	{
		return \count($this->slugSegments) > 1
			? \implode("/", \array_slice($this->slugSegments, 0, -1))
			: null;
	}

	/**
	 * Tries to get the locale from the slug.
	 * It will read the first segment in the slug and check if it syntactically could be a locale.
	 */
	public function getLocaleFromSlug () : ?string
	{
		$firstSegment = $this->slugSegments[0] ?? null;

		return null !== $firstSegment && 1 === \preg_match('~^\\w+(-\\w+)?~', $firstSegment)
			? $firstSegment
			: null;
	}

	/**
	 * @return string[]
	 */
	public function getSlugSegments () : array
	{
		return $this->slugSegments;
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
