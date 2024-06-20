<?php declare(strict_types=1);

namespace Torr\Storyblok\Translation;

final class LocaleHelper
{
	/**
	 * Returns whether the given string is a syntactically valid locale.
	 */
	public static function isValidLocale (string $value) : bool
	{
		return 1 === preg_match('~^\\w{2,3}(-\\w{2,})?$~', $value);
	}
}
