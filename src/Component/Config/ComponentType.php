<?php declare(strict_types=1);

namespace Torr\Storyblok\Component\Config;

enum ComponentType
{
	case Standalone;

	case Nested;

	case Universal;

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
			self::Universal => [
				"is_root" => true,
				"is_nestable" => true,
			],
		};
	}
}
