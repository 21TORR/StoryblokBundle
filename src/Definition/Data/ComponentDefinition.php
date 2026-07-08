<?php declare(strict_types=1);

namespace Torr\Storyblok\Definition\Data;

use Torr\Storyblok\Component\Config\ComponentType;

/**
 * The definition of a whole component
 */
final readonly class ComponentDefinition
{
	/**
	 */
	public function __construct (
		public string $storyClass,
		public string $key,
		public string $label,
		public ComponentType $type,
		/** @var array<string, FieldDefinition> $fields */
		public array $fields,
	) {}

	/**
	 */
	public function getField (string $key) : ?FieldDefinition
	{
		return $this->fields[$key] ?? null;
	}
}
