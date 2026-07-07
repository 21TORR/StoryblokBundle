<?php declare(strict_types=1);

namespace Torr\Storyblok\Component\Config;

enum ComponentType
{
	// "Universal" not supported by design
	case Standalone;
	case Nested;

	public function toManagementApiData () : array
	{
		return match ($this)
		{
			self::Standalone => [
				"is_root" => true,
				"is_nestable" => false,
			],
			self::Nested => [
				"is_root" => false,
				"is_nestable" => true,
			],
		};
	}
}
