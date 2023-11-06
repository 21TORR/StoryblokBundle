<?php declare(strict_types=1);

namespace Torr\Storyblok\Mapping\Embed;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class EmbeddedStory
{
	public function __construct (
		public readonly string $prefix,
		public readonly string $label,
	) {}
}
