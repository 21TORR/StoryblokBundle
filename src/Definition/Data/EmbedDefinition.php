<?php declare(strict_types=1);

namespace Torr\Storyblok\Definition\Data;

/**
 * Definition of an embedded component.
 */
final readonly class EmbedDefinition
{
	/**
	 */
	public function __construct (
		public string $embeddedClass,
		public string $label,
		public string $key,
		public array $fields,
	) {}
}
