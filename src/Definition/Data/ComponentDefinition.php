<?php declare(strict_types=1);

namespace Torr\Storyblok\Definition\Data;

use Torr\Storyblok\Component\Config\ComponentType;

/**
 * @final
 */
readonly class ComponentDefinition
{
	/**
	 */
	public function __construct (
		public string $storyClass,
		public string $label,
		public string $key,
		public ComponentType $type,
		public array $fields,
	) {}
}
