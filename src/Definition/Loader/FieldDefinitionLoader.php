<?php declare(strict_types=1);

namespace Torr\Storyblok\Definition\Loader;

use Torr\Storyblok\Definition\Data\FieldDefinition;
use Torr\Storyblok\Definition\DefinitionRegistry;
use Torr\Storyblok\Definition\Exception\InvalidFieldDefinitionException;
use Torr\Storyblok\Definition\Field\MappedField;
use Torr\Storyblok\Definition\Mapping\EmbeddedField;
use Torr\Storyblok\Definition\Mapping\Required;
use Torr\Storyblok\Definition\Mapping\Translatable;

/**
 * @final
 */
readonly class FieldDefinitionLoader
{
	/**
	 * @return FieldDefinition[]
	 */
	public function loadFieldDefinitions (
		DefinitionRegistry $registry,
		\ReflectionProperty $property,
	) : array
	{
		$field = $this->fetchFieldAttribute($property);

		if (null === $field)
		{
			return [];
		}

		if ($field instanceof EmbeddedField)
		{
			$type = $property->getSettableType();

			if (null === $type)
			{
				throw new InvalidFieldDefinitionException(
					"Properties with an Embed Field may only use a single property type",
				);
			}

			$registry->registerEmbedded((string) $type);
			$key = $field->key ?? $property->getName() . "_";

			return [
				$key => new FieldDefinition(
					key: $key,
					label: $field->label,
					propertyPath: $property->getName(),
					data: $field->toManagementApiData(),
					mapping: $field,
					propertyType: (string) $type,
					required: $this->fetchFirstAttribute($property, Required::class),
					translatable: $this->fetchFirstAttribute($property, Translatable::class),
				),
			];
		}

		$key = $field->key ?? $property->getName();

		return [
			$key => new FieldDefinition(
				key: $key,
				label: $field->label,
				propertyPath: $property->getName(),
				data: $field->toManagementApiData(),
				mapping: $field,
				propertyType: null,
				required: $this->fetchFirstAttribute($property, Required::class),
				translatable: $this->fetchFirstAttribute($property, Translatable::class),
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
			if (is_a($attribute->getName(), MappedField::class, true))
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
	 *
	 * @param class-string<AttributeType> $attribute
	 *
	 * @return AttributeType|null
	 */
	private function fetchFirstAttribute (\ReflectionProperty $property, string $attribute) : ?object
	{
		return $property->getAttributes($attribute)[0]?->newInstance() ?? null;
	}
}
