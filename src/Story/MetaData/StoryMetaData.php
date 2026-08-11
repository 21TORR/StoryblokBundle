<?php declare(strict_types=1);

namespace Torr\Storyblok\Story\MetaData;

use Torr\Storyblok\Translation\LocaleHelper;

final readonly class StoryMetaData extends AbstractMetaData
{
	private array $slugSegments;
	public string $fullSlug;

	/**
	 * @param list<array{id: int, name: string, slug: string, published: bool, full_slug: string, is_folder: bool, parent_id: int}> $alternates
	 */
	public function __construct (
		string $uuid,
		string $type,
		?string $previewData,
		public string $name,
		string $fullSlug,
		public \DateTimeImmutable $createdAt,
		public ?\DateTimeImmutable $firstPublishedAt,
		public ?\DateTimeImmutable $publishedAt,
		public string $id,
		public bool $isStartPage,
		public string $locale,
		public ?int $position,
		private array $alternates,
		string $spaceId,
		public int $localeLevel,
	) {
		parent::__construct($uuid, $type, $spaceId, $previewData);
		$this->fullSlug = rtrim($fullSlug, "/");
		$this->slugSegments = explode("/", $this->fullSlug);
	}

	/**
	 *
	 */
	public function getSlug () : string
	{
		return $this->slugSegments[\count($this->slugSegments) - 1];
	}

	/**
	 * Returns the slug of the parent
	 * (without a trailing slash).
	 */
	public function getParentSlug () : ?string
	{
		return \count($this->slugSegments) > 1
			? implode("/", \array_slice($this->slugSegments, 0, -1))
			: null;
	}

	/**
	 * Tries to get the locale from the slug.
	 * It will read the first segment in the slug and check if it syntactically could be a locale.
	 */
	public function getLocaleFromSlug () : ?string
	{
		$firstSegment = $this->slugSegments[$this->localeLevel] ?? null;

		return null !== $firstSegment && LocaleHelper::isValidLocale($firstSegment)
			? $firstSegment
			: null;
	}

	/**
	 * @return list<array{id: int, name: string, slug: string, published: bool, full_slug: string, is_folder: bool, parent_id: int, locale: ?string}>
	 */
	public function getAlternateLanguages () : array
	{
		$result = [];

		foreach ($this->alternates as $alternate)
		{
			$slugSegments = explode("/", rtrim($alternate["full_slug"], "/"));
			$locale = $slugSegments[$this->localeLevel];

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
					: rtrim($slug, "/");
			}
		}

		return $mapping;
	}

	public function getPreviewData () : ?string
	{
		return $this->previewData;
	}
}
