<?php declare(strict_types=1);

namespace Torr\Storyblok\Definition\Field;

use Torr\Storyblok\Exception\Component\InvalidComponentDefinitionException;
use Torr\Storyblok\Field\FieldType;
use Torr\Storyblok\Mapping\Embed\EmbeddedStory;

final readonly class EmbeddedFieldDefinition
{
	public function __construct (
		public EmbeddedStory $definition,
		public string $property,
		public string $embedClass,
		/** @type array<string, FieldDefinition> */
		public array $fields,
	) {}


	/**
	 *
	 */
	public function generateManagementApiDataForAllFields () : array
	{
		$keys = [];
		$schemas = [];
		$prefix = \rtrim($this->definition->prefix, "_") . "_";

		foreach ($this->fields as $field)
		{
			$fullKey = $prefix . $field->field->key;

			if (\array_key_exists($fullKey, $schemas))
			{
				throw new InvalidComponentDefinitionException(\sprintf(
					"Found multiple definitions for field name '%s' in embed '%s'",
					$fullKey,
					$this->embedClass,
				));
			}

			$schemas[$fullKey] = $field->generateManagementApiData();
			$keys[] = $fullKey;
		}


		$schemas[$prefix . "_embed"] = [
			"type" => FieldType::Section->value,
			"display_name" => $this->definition->label,
			"keys" => $keys,
		];

		return $schemas;
	}
}
