<?php declare(strict_types=1);

namespace Torr\Storyblok\Definition\Mapping;

/**
 * The attribute for defining a component.
 *
 * The type of the component is resolved using the base class of the component class.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class Component
{
	/**
	 */
	public function __construct (
		public string $key,
		public string $label,
		public ?string $description = null,
	) {}
}
