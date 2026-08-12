<?php

namespace Torr\Storyblok\Helper;

enum EnumHelper
{
	/**
	 * @phpstan-return $value is null ? null : string
	 */
	public static function value (string|\BackedEnum|null $value) : ?string
	{
		return $value instanceof \BackedEnum
			? $value->value
			: $value;
	}
}
