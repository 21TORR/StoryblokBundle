<?php declare(strict_types=1);

namespace Torr\Storyblok\Definition\Component\Reflection;

use Torr\Storyblok\Exception\Component\InvalidComponentDefinitionException;

final class ReflectionHelper
{
	/**
	 * Instantiates and returns a single attribute
	 *
	 * @template AttributeType of object
	 *
	 * @param class-string<AttributeType> $attributeClass
	 * @return AttributeType
	 *
	 * @throws InvalidComponentDefinitionException
	 */
	public function getRequiredSingleAttribute (
		\ReflectionClass|\ReflectionProperty $element,
		string $attributeClass,
	) : object
	{
		$attribute = $this->getOptionalSingleAttribute($element, $attributeClass);

		if (null === $attribute)
		{
			throw new InvalidComponentDefinitionException(\sprintf(
				"Could not find required attribute of type '%s' on class '%s'.",
				$attributeClass,
				$element->getName(),
			));
		}

		return $attribute;
	}


	/**
	 * Instantiates and returns a single attribute
	 *
	 * @template AttributeType of object
	 *
	 * @param class-string<AttributeType> $attributeClass
	 * @return AttributeType|null
	 *
	 * @throws InvalidComponentDefinitionException
	 */
	public function getOptionalSingleAttribute (
		\ReflectionClass|\ReflectionProperty $element,
		string $attributeClass,
	) : ?object
	{
		$attributes = $this->getAttributes($element, $attributeClass);

		if (\count($attributes) > 1)
		{
			throw new InvalidComponentDefinitionException(\sprintf(
				"Found multiple instances of attribute '%s', but expected only one.",
				$attributeClass,
			));
		}

		return $attributes[0] ?? null;
	}


	/**
	 * @template AttributeType of object
	 *
	 * @param class-string<AttributeType> $attributeClass
	 * @return array<AttributeType>
	 */
	public function getAttributes (
		\ReflectionClass|\ReflectionProperty $element,
		string $attributeClass,
	) : array
	{
		$result = [];

		foreach ($element->getAttributes() as $reflectionAttribute)
		{
			if (\is_a($reflectionAttribute->getName(), $attributeClass, true))
			{
				/** @var AttributeType $attribute */
				$attribute = $reflectionAttribute->newInstance();
				$result[] = $attribute;
			}
		}

		return $result;
	}
}
