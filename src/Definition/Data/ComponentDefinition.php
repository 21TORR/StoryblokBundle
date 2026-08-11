<?php declare(strict_types=1);

namespace Torr\Storyblok\Definition\Data;

use Torr\Storyblok\Component\Config\ComponentType;
use Torr\Storyblok\Definition\Exception\InvalidComponentDefinitionException;

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
		/** @var array<string, FieldDefinition|EmbedDefinition> $fields */
		public array $fields,
		public ?string $description = null,
		public array $tags = [],
		public string|\BackedEnum|null $folder = null,
	)
	{
		if ([] === $this->fields)
		{
			throw new InvalidComponentDefinitionException(\sprintf(
				"Can't create component without fields in story '%s'.",
				$this->storyClass,
			));
		}
	}

	/**
	 */
	public function getField (string $key) : ?FieldDefinition
	{
		return $this->fields[$key] ?? null;
	}
}
