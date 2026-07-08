<?php declare(strict_types=1);

namespace Torr\Storyblok\Definition\Data;

use Torr\Storyblok\Definition\Field\MappedField;
use Torr\Storyblok\Definition\Mapping\Required;
use Torr\Storyblok\Definition\Mapping\Translatable;
use Torr\Storyblok\Field\FieldType;

/**
 * Definition of a single field
 */
final readonly class FieldDefinition
{
	/**
	 */
	public function __construct (
		public string $key,
		public string $label,
		public string $propertyPath,
		public array $data,
		public MappedField $field,
		public mixed $propertyType,
		public ?Required $required = null,
		public ?Translatable $translatable = null,
	) {}

	/**
	 */
	public function getFieldType () : FieldType
	{
		return $this->field->getType();
	}

	public function getFieldDefinitions ()
	{

	}
}
