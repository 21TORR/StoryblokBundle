<?php declare(strict_types=1);

namespace Torr\Storyblok\Story;

use Torr\Storyblok\Exception\Story\StoryHydrationFailed;
use Torr\Storyblok\Translation\LocaleHelper;

final class StoryMetaData
{
	private readonly array $data;
	private readonly array $slugSegments;
	private readonly ?string $previewData;

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
		$this->previewData = $data["content"]["_editable"] ?? null;
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

		return null !== $firstSegment && LocaleHelper::isValidLocale($firstSegment)
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

	/**
	 */
	public function getPosition () : ?int
	{
		return $this->data["position"] ?? null;
	}

	/**
	 * @return array<array{id: int, name: string, slug: string, published: bool, full_slug: string, is_folder: bool, parent_id: int, locale: ?string}>
	 */
	public function getAlternateLanguages () : array
	{
		$result = [];

		/** @var array{id: int, name: string, slug: string, published: bool, full_slug: string, is_folder: bool, parent_id: int} $alternate */
		foreach (($this->data["alternates"] ?? []) as $alternate)
		{
			$slug = $alternate["full_slug"];
			$locale = \mb_substr($slug, 0, \strpos($slug, "/") ?: null);

			$alternate["locale"] = LocaleHelper::isValidLocale($locale)
				? $locale
				: null;

			$result[] = $alternate;
		}

		return $result;
	}

	/**
	 * Returns the mapping of locale to full slug for alternative translated versions of this story.
	 *
	 * @return array<string, string> locale => full_slug
	 */
	public function getTranslatedDocumentsMapping () : array
	{
		$mapping = [];

		foreach ($this->getAlternateLanguages() as $alternateLanguage)
		{
			if (null !== $alternateLanguage["locale"])
			{
				$slug = $alternateLanguage["full_slug"];
				$mapping[$alternateLanguage["locale"]] = $alternateLanguage["is_folder"]
					? $slug
					: \rtrim($slug, "/");
			}
		}

		return $mapping;
	}

	public function getPreviewData () : ?string
	{
		return $this->previewData;
	}
}
