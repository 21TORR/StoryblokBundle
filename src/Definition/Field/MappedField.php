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
		public ?string $key = null,
		public string $label,
	) {}

	/**
	 * Returns the Storyblok type
	 */
	abstract public function getType () : FieldType;

	/**
	 * Returns the field data VO for the API
	 */
	abstract public function createManagementApiData () : ?array;

	/**
	 * @param string[] $contentPathHierarchy The path to the given element
	 */
	public function validateValue (
		string $contentPath,
		array $storyData,
		FieldDefinition $fieldDefinition,
		ComponentContext $context,
		array $contentPathHierarchy,
	) : void {}

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

	/**
	 */
	protected function mergeManagementData (array $config) : array
	{
		$filtered = array_filter(
			$config,
			static fn (mixed $value) => null !== $value,
		);

		$filtered["type"] = $this->getType()->value;
		$filtered["display_name"] = $this->label;

		return $filtered;
	}
}
