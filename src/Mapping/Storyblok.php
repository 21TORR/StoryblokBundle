<?php declare(strict_types=1);

namespace Torr\Storyblok\Mapping;

use function Symfony\Component\String\u;

/**
 * A storyblok content element
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class Storyblok
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
	public function getName () : string
	{
		return $this->name ?? u($this->key)
			->title()
			->toString();
	}
}
