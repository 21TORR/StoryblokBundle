<?php declare(strict_types=1);

namespace Torr\Storyblok\Management\Generator;

use Torr\Storyblok\Definition\Data\FieldDefinition;
use Torr\Storyblok\Definition\DefinitionRegistry;
use Torr\Storyblok\Definition\Mapping\EmbeddedField;
use Torr\Storyblok\Management\Exception\ComponentManagementDataGenerationFailedException;

/**
 */
final class FieldSchemaCollection
{
	public private(set) array $schemas = [];

	/**
	 */
	public function __construct (
		private readonly DefinitionRegistry $registry,
	) {}


	public function add (FieldDefinition $definition, string $keyPrefix = "") : void
	{
		if ($definition->mapping instanceof EmbeddedField)
		{
			$this->addEmbed($definition, $keyPrefix);
			return;
		}

		$this->addFieldSchema($definition, $keyPrefix);
	}

	/**
	 *
	 */
	private function addEmbed (FieldDefinition $definition, string $keyPrefix = "") : void
	{
		$embeddedDefinition = $this->registry->getEmbeddedDefinition($definition->propertyType);

		if (null === $embeddedDefinition)
		{
			throw new ComponentManagementDataGenerationFailedException(
				\sprintf(
					"Could not find embedded definition for '%s'",
					$definition->propertyType,
				)
			);
		}

		$keyPrefix .= $definition->key;

		foreach ($embeddedDefinition->fields as $field)
		{
			$this->add($field, $keyPrefix);
		}
	}

	/**
	 */
	public function addFieldSchema (FieldDefinition $field, string $keyPrefix = "") : void
	{
		$mapping = $field->mapping;

		$this->addSchema($keyPrefix . $field->key, [
			"type" => $mapping->getType()->value,
			"display_name" => $field->label,
			"default_value" => $mapping->defaultValue,
			...$mapping->toManagementApiData(),
		]);
	}

	/**
	 *
	 */
	private function addSchema (string $key, array $schema) : void
	{
		if (\array_key_exists($key, $this->schemas))
		{
			throw new ComponentManagementDataGenerationFailedException(\sprintf(
				"Invalid component configuration: field key '%s' used more than once",
				$key,
			));
		}

		$schema["pos"] = \count($this->schemas);
		$this->schemas[$key] = $schema;
	}
}
