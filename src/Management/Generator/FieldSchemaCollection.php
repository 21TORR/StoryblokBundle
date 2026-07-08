<?php declare(strict_types=1);

namespace Torr\Storyblok\Management\Generator;

use Torr\Storyblok\Definition\Data\FieldDefinition;
use Torr\Storyblok\Definition\DefinitionRegistry;
use Torr\Storyblok\Definition\Mapping\EmbeddedField;
use Torr\Storyblok\Management\Exception\ComponentManagementDataGenerationFailedException;
use function PHPUnit\Framework\assertSame;

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

	/**
	 * @return string[] the keys of the added schemas
	 */
	public function add (FieldDefinition $definition, string $keyPrefix = "") : array
	{
		if ($definition->mapping instanceof EmbeddedField)
		{
			return $this->addEmbed($definition, $keyPrefix);
		}

		return $this->addFieldSchema($definition, $keyPrefix);
	}

	/**
	 * @return string[] the keys of the added schemas
	 */
	private function addEmbed (FieldDefinition $definition, string $keyPrefix = "") : array
	{
		$embeddedDefinition = $this->registry->getEmbeddedDefinition($definition->propertyType);
		$mapping = $definition->mapping;
		\assert($mapping instanceof EmbeddedField);

		$result = [];

		if (null === $embeddedDefinition)
		{
			throw new ComponentManagementDataGenerationFailedException(
				\sprintf(
					"Could not find embedded definition for '%s'",
					$definition->propertyType,
				)
			);
		}

		$keyPrefix .= $definition->key . "_";

		foreach ($embeddedDefinition->fields as $field)
		{
			$addedFields = $this->add($field, $keyPrefix);

			$result = \array_merge($result, $addedFields);
		}

		if ($mapping->group)
		{
			$this->addSchema($definition->key, [
				...$definition->mapping->toManagementApiData(),
				"keys" => $result,
			]);
		}

		return $result;
	}

	/**
	 * @return string[] the keys of the added schemas
	 */
	public function addFieldSchema (FieldDefinition $field, string $keyPrefix = "") : array
	{
		$fullFieldKey = $keyPrefix . $field->key;
		$this->addSchema($keyPrefix . $field->key, $field->mapping->toManagementApiData());

		return [$fullFieldKey];
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
