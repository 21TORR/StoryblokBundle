<?php declare(strict_types=1);

namespace Torr\Storyblok\Definition\Data;

use Torr\Storyblok\Component\Config\ComponentType;

/**
 * The definition of a whole component
 */
final readonly class ComponentDefinition
{
	/**
	 * @param array<string, FieldDefinition> $fields
	 */
	public function __construct (
		public string $storyClass,
		public string $label,
		public string $key,
		public ComponentType $type,
		public array $fields,
	) {}

	/**
	 */
	public function getField (string $key) : ?FieldDefinition
	{
		return $this->fields[$key] ?? null;
	}
}
