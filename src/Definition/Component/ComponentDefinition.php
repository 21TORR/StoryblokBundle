<?php declare(strict_types=1);

namespace Torr\Storyblok\Definition\Component;

use Torr\Storyblok\Definition\Field\EmbeddedFieldDefinition;
use Torr\Storyblok\Definition\Field\FieldDefinition;
use Torr\Storyblok\Exception\Component\InvalidComponentDefinitionException;
use Torr\Storyblok\Mapping\StoryBlok;
use Torr\Storyblok\Mapping\StoryDocument;
use Torr\Storyblok\Story\Document;
use function Symfony\Component\String\u;

final readonly class ComponentDefinition
{
	/**
	 */
	public function __construct (
		public StoryDocument|StoryBlok $definition,
		/** @type class-string<Document> */
		public string $storyClass,
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
		return $this->definition->name
			?? u($this->definition->key)
				->title()
				->toString();
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
			...$this->definition->getComponentTypeApiData(),
		];
	}
}
