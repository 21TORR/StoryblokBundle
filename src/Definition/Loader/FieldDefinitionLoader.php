<?php declare(strict_types=1);

namespace Torr\Storyblok\Definition\Loader;

use Torr\Storyblok\Definition\Data\FieldDefinition;
use Torr\Storyblok\Definition\Exception\InvalidFieldDefinitionException;
use Torr\Storyblok\Definition\Field\MappedField;

/**
 * @final
 */
readonly class FieldDefinitionLoader
{
	/**
	 * @return FieldDefinition[]
	 */
	public function loadFieldDefinitions (\ReflectionProperty $property) : array
	{
		$field = $this->fetchFieldAttribute($property);

		if (null === $field)
		{
			return [];
		}

		return [
			$field->key => new FieldDefinition(
				key: $field->key,
				label: $field->label,
				type: $field->getType(),
				propertyPath: $property->getName(),
				data: $field->getManagementApiData(),
				field: $field,
			),
		];
	}

	/**
	 *
	 */
	private function fetchFieldAttribute (\ReflectionProperty $property) : ?MappedField
	{
		$field = null;

		foreach ($property->getAttributes() as $attribute)
		{
			if (\is_a($attribute->getName(), MappedField::class, true))
			{
				if (null !== $field)
				{
					throw new InvalidFieldDefinitionException(\sprintf(
						"Property '%s::$%s' can't have multiple field attributes",
						$property->getDeclaringClass()->getName(),
						$property->getName(),
					));
				}

				$field = $attribute->newInstance();
				\assert($field instanceof MappedField);
			}
		}

		return $field;
	}


	/**
	 * @template AttributeType of object
	 * @param class-string<AttributeType> $attribute
	 *
	 * @return AttributeType|null
	 */
	private function loadAttribute (\ReflectionClass $storyClass, string $attribute) : ?object
	{
		return $storyClass->getAttributes($attribute)[0]?->newInstance() ?? null;
	}
}
