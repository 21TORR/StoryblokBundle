<?php declare(strict_types=1);

namespace Torr\Storyblok\Component;

final class ComponentHelper
{
	/**
	 * Returns whether the given key is a reserved one.
	 */
	public static function isReservedKey (string $key) : bool
	{
		return "component" === $key;
	}
}
