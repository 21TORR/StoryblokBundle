<?php declare(strict_types=1);

namespace Torr\Storyblok\Mapping;

use Torr\Storyblok\Story\Document;

/**
 * A top-level storyblok content element
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class StoryDocument
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
			"is_root" => true,
			"is_nestable" => false,
		];
	}


	/**
	 * @return class-string
	 */
	public function getRequiredExtendedClass () : string
	{
		return Document::class;
	}
}
