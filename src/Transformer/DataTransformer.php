<?php declare(strict_types=1);

namespace Torr\Storyblok\Transformer;

final class DataTransformer
{
	/**
	 * @param string|null $value
	 * @return string|null
	 */
	public static function normalizeOptionalString (?string $value) : ?string
	{
		if (null === $value)
		{
			return null;
		}

		// Normalize to null. Trim for checking, but don't trim data if is not empty, just to be sure.
		return "" !== \trim($value)
			? $value
			: null;
	}
}
