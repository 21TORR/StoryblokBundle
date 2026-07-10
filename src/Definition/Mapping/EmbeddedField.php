<?php declare(strict_types=1);

namespace Torr\Storyblok\Definition\Mapping;

use Storyblok\ManagementApi\Data\Fields\Schema\FieldGeneric;
use Storyblok\ManagementApi\Data\Fields\Schema\FieldInterface;
use Storyblok\ManagementApi\Data\Fields\Schema\FieldSection;
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
		public string $label,
		?string $key = null,
		public bool $group = false,
	)
	{
		parent::__construct($key);
	}

	/**
	 *
	 */
	#[\Override]
	public function createApiData (string $key) : ?FieldGeneric
	{
		if $this->group)
		{
			return new FieldSection($key)
				->setDisplayName($this->label);
		}

		return null;
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
