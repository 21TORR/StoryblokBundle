<?php declare(strict_types=1);

namespace Torr\Storyblok\Release;

enum ReleaseVersion : string
{
	case PUBLISHED = "published";
	case DRAFT = "draft";

	/**
	 * Helper to create the version from a boolean
	 */
	public static function createFromPreviewFlag (bool $isPreview) : self
	{
		return $isPreview
			? self::DRAFT
			: self::PUBLISHED;
	}
}
