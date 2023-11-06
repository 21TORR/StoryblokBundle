<?php declare(strict_types=1);

namespace Torr\Storyblok\Definition\Field;

use Torr\Storyblok\Exception\Component\InvalidComponentDefinitionException;
use Torr\Storyblok\Field\FieldType;
use Torr\Storyblok\Mapping\Embed\EmbeddedStory;

final readonly class EmbeddedFieldDefinition
{
	/**
	 * @type array<string, FieldDefinition>
	 */
	public array $fields;

	/**
	 * @param array<string, FieldDefinition> $fields
	 */
	public function __construct (
		public EmbeddedStory $definition,
		public string $property,
		/** @var class-string */
		public string $embedClass,
		array $fields,
	)
	{
		$transformed = [];

		foreach ($fields as $key => $field)
		{
			$transformed[$this->definition->prefix . $key] = $field;
		}

		$this->fields = $transformed;
	}


	/**
	 *
	 */
	public function generateManagementApiDataForAllFields () : array
	{
		$schemas = [];
		$prefix = \rtrim($this->definition->prefix, "_") . "_";

		foreach ($this->fields as $key => $field)
		{
			$schemas[$key] = $field->generateManagementApiData();
		}


		$schemas[$prefix . "_embed"] = [
			"type" => FieldType::Section->value,
			"display_name" => $this->definition->label,
			"keys" => \array_keys($this->fields),
		];

		return $schemas;
	}
}
