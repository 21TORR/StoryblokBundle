<?php declare(strict_types=1);

namespace Torr\Storyblok\Definition\Data;

use Torr\Storyblok\Context\ComponentContext;
use Torr\Storyblok\Definition\Field\MappedField;
use Torr\Storyblok\Field\FieldType;

/**
 * @final
 */
readonly class FieldDefinition
{
	/**
	 */
	public function __construct (
		public string $key,
		public string $label,
		public FieldType $type,
		public string $propertyPath,
		public array $data,
		public MappedField $field,
	) {}


	/**
	 */
	public function transformStoryblokValue (
		mixed $value,
		ComponentContext $context,
	) : mixed
	{
		return $this->field->transformStoryblokValue($value, $context);
	}
}
