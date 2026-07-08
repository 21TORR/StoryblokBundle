<?php declare(strict_types=1);

namespace Torr\Storyblok\Definition\Mapping;

use Torr\Storyblok\Context\ComponentContext;
use Torr\Storyblok\Definition\Data\FieldDefinition;
use Torr\Storyblok\Definition\Field\MappedField;
use Torr\Storyblok\Field\FieldType;
use Torr\Storyblok\Story\Hydrator\StoryHydrator;

/**
 * @final
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
readonly class EmbeddedField extends MappedField
{
	/**
	 */
	public function __construct (
		string $label,
		?string $key = null,
	)
	{
		parent::__construct($label, $key);
	}

	/**
	 *
	 */
	#[\Override]
	public function getType () : FieldType
	{
		return FieldType::Section;
	}

	/**
	 *
	 */
	#[\Override]
	public function toManagementApiData () : array
	{
		return array_replace(
			parent::toManagementApiData(),
			[
			],
		);
	}

	/**
	 *
	 */
	#[\Override]
	public function transformStoryblokValue (
		string $contentPath,
		array $storyData,
		FieldDefinition $definition,
		ComponentContext $context,
		StoryHydrator $hydrator,
	) : ?object
	{
		return $hydrator->hydrateEmbed(
			embedClass: $definition->propertyType,
			contentPathPrefix: $contentPath,
			data: $storyData,
		);
	}
}
