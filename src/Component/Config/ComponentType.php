<?php declare(strict_types=1);

namespace Torr\Storyblok\Component\Config;

use Storyblok\ManagementApi\Data\Component;

enum ComponentType
{
	// "Universal" not supported by design
	case Standalone;
	case Nested;

	/**
	 */
	public function toManagementApiComponent () : Component
	{
		return match ($this)
		{
			self::Standalone => Component::contentType("empty"),
			self::Nested => Component::nestable("empty"),
		};
	}
}
