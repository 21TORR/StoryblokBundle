<?php declare(strict_types=1);

namespace Torr\Storyblok\Definition\Field;

use Storyblok\ManagementApi\Data\Fields\Schema\FieldGeneric;
use Torr\Storyblok\Context\ComponentContext;
use Torr\Storyblok\Definition\Data\FieldDefinition;
use Torr\Storyblok\Story\Hydrator\StoryHydrator;

abstract readonly class MappedField
{
	/**
	 */
	public function __construct (
		public ?string $key = null,
	) {}


	/**
	 * Returns the field data VO for the API
	 */
	abstract public function createApiData (string $key) : ?FieldGeneric;


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
}
