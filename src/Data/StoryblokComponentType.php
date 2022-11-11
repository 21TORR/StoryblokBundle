<?php declare(strict_types=1);

namespace Torr\Storyblok\Data;

enum StoryblokComponentType
{
	case ContentType;

	case ModuleType;

	case UniversalType;

	public function toApiData () : array
	{
		return match ($this)
		{
			self::ContentType => [
				"is_root" => true,
				"is_nestable" => false,
			],
			self::ModuleType => [
				"is_root" => false,
				"is_nestable" => true,
			],
			self::UniversalType => [
				"is_root" => true,
				"is_nestable" => true,
			],
		};
	}
}
