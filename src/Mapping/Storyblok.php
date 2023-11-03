<?php declare(strict_types=1);

namespace Torr\Storyblok\Mapping;

use Torr\Storyblok\Component\Config\ComponentType;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class Storyblok
{
	public function __construct (
		public string $key,
		public string $name,
		public ComponentType $type,
		/** @var array<string|\BackedEnum> */
		public array $tags = [],
		public ?string $previewField = null,
		public string|\BackedEnum|null $group = null,
	) {}
}
