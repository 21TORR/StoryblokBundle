<?php declare(strict_types=1);

namespace Torr\Storyblok\Mapping;

use Torr\Storyblok\Story\Blok;

/**
 * A nestable storyblok content element
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
final class StoryBlok
{
	public function __construct (
		public string $key,
		public ?string $name = null,
		/** @var array<string|\BackedEnum> */
		public array $tags = [],
		public ?string $previewField = null,
		public string|\BackedEnum|null $group = null,
	) {}

	/**
	 *
	 */
	public function getComponentTypeApiData () : array
	{
		return [
			"is_root" => false,
			"is_nestable" => true,
		];
	}


	/**
	 * @return class-string
	 */
	public function getRequiredExtendedClass () : string
	{
		return Blok::class;
	}
}
