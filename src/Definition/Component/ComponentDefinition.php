<?php declare(strict_types=1);

namespace Torr\Storyblok\Definition\Component;

use Torr\Storyblok\Definition\Field\FieldDefinition;
use Torr\Storyblok\Exception\Component\InvalidComponentDefinitionException;
use Torr\Storyblok\Mapping\Storyblok;
use Torr\Storyblok\Story\Story;

final readonly class ComponentDefinition
{
	/**
	 */
	public function __construct (
		public Storyblok $definition,
		/** @type class-string<Story> */
		public string $storyClass,
		/** @type array<string, FieldDefinition> */
		public array $fields,
	)
	{
		if (null !== $this->definition->previewField && !\array_key_exists($this->definition->previewField, $this->fields))
		{
			throw new InvalidComponentDefinitionException(\sprintf(
				"Can't use unknown field '%s' as preview field in story '%s'",
				$this->definition->previewField,
				$this->storyClass,
			));
		}
	}

	/**
	 */
	public function getDisplayName () : string
	{
		return $this->definition->name;
	}

	/**
	 */
	public function getKey () : string
	{
		return $this->definition->key;
	}


	public function generateManagementApiData () : array
	{
		$fields = [];

		foreach ($this->fields as $field)
		{
			$fieldData = $field->generateManagementApiData();
			$fields[$field->field->key] = $fieldData;
		}

		return [
			"name" => $this->definition->key,
			"real_name" => $this->definition->key,
			"display_name" => $this->definition->name,
			"schema" => $fields,
			"preview_field" => $this->definition->previewField,
			...$this->definition->type->generateManagementApiData(),
		];
	}
}
