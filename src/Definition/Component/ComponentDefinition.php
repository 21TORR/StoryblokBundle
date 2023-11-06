<?php declare(strict_types=1);

namespace Torr\Storyblok\Definition\Component;

use Torr\Storyblok\Definition\Field\EmbeddedFieldDefinition;
use Torr\Storyblok\Definition\Field\FieldDefinition;
use Torr\Storyblok\Exception\Component\InvalidComponentDefinitionException;
use Torr\Storyblok\Mapping\Storyblok;
use Torr\Storyblok\Story\StoryContent;
use Torr\Storyblok\Story\StoryDocument;

final readonly class ComponentDefinition
{
	/**
	 */
	public function __construct (
		public Storyblok $definition,
		/** @type class-string<StoryDocument|StoryContent> */
		public string $storyClass,
		public bool $isDocument,
		/** @type array<string, FieldDefinition|EmbeddedFieldDefinition> */
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
	public function getName () : string
	{
		return $this->definition->getName();
	}

	/**
	 */
	public function getKey () : string
	{
		return $this->definition->key;
	}


	public function generateManagementApiData () : array
	{
		$fieldSchemas = [];
		$position = 0;

		foreach ($this->fields as $field)
		{
			$additionalFields = $field instanceof FieldDefinition
				? [
					$field->field->key => $field->generateManagementApiData(),
				]
				: $field->generateManagementApiDataForAllFields();

			foreach ($additionalFields as $key => $fieldData)
			{
				if (\array_key_exists($key, $fieldSchemas))
				{
					throw new InvalidComponentDefinitionException(\sprintf(
						"Found multiple definitions for field name '%s' in type '%s'",
						$key,
						$this->storyClass,
					));
				}

				// consistently set the position for all fields
				$fieldData["pos"] = ++$position;
				$fieldSchemas[$key] = $fieldData;
			}
		}

		return [
			"name" => $this->definition->key,
			"real_name" => $this->definition->key,
			"display_name" => $this->getName(),
			"schema" => $fieldSchemas,
			"preview_field" => $this->definition->previewField,
			"is_root" => $this->isDocument,
			"is_nestable" => !$this->isDocument,
		];
	}
}
