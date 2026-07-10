<?php declare(strict_types=1);

namespace Torr\Storyblok\Definition\Data;

use Storyblok\ManagementApi\Data\Fields\Schema\FieldGeneric;
use Torr\Storyblok\Definition\Field\MappedField;
use Torr\Storyblok\Definition\Mapping\Required;
use Torr\Storyblok\Definition\Mapping\Translatable;

/**
 * Definition of a single field
 */
final readonly class FieldDefinition
{
	/**
	 */
	public function __construct (
		public string $key,
		public string $propertyPath,
		public MappedField $mapping,
		public mixed $propertyType,
		public ?Required $required = null,
		public ?Translatable $translatable = null,
	) {}


	/**
	 * Returns the field data VO for the API
	 */
	public function createApiData (string $key) : ?FieldGeneric
	{
		return $this->mapping->createApiData($key)
			?->setRequired(null !== $this->required);
	}
}
