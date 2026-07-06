<?php declare(strict_types=1);

namespace Torr\Storyblok\Definition\Data;

/**
 * @final
 */
readonly class ComponentDefinition
{
	/**
	 */
	public function __construct (
		public string $label,
		public string $key,
	) {}
}
