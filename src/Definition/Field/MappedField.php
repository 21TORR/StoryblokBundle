<?php declare(strict_types=1);

namespace Torr\Storyblok\Definition\Field;

use Torr\Storyblok\Context\ComponentContext;
use Torr\Storyblok\Definition\Data\FieldDefinition;
use Torr\Storyblok\Field\FieldType;
use Torr\Storyblok\Story\Hydrator\StoryHydrator;

abstract readonly class MappedField
{
	/**
	 */
	public function __construct (
		public string $label,
		public ?string $key = null,
		public mixed $defaultValue = null,
		public bool $translatable = false,
	) {}

	/**
	 * Returns the Storyblok type
	 */
	abstract public function getType () : FieldType;

	/**
	 *
	 */
	public function toManagementApiData () : array
	{
		return [
			"type" => $this->getType()->value,
			"display_name" => $this->label,
			"default_value" => $this->defaultValue,
		];
	}

	/**
	 * @param string[] $contentPathHierarchy The path to the given element
	 */
	public function validateValue (
		string $contentPath,
		array $storyData,
		FieldDefinition $fieldDefinition,
		ComponentContext $context,
		array $contentPathHierarchy,
	) {}

	/**
	 */
	public function transformStoryblokValue (
		string $contentPath,
		array $storyData,
		FieldDefinition $definition,
		ComponentContext $context,
		StoryHydrator $hydrator,
	) : mixed
	{
		return $storyData[$contentPath] ?? null;
	}
}
