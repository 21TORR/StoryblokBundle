<?php declare(strict_types=1);

namespace Torr\Storyblok\Reflection;

/**
 *
 */
final class ReflectionHelper
{
	/**
	 * @template AttributeObject of object
	 *
	 * @param class-string<AttributeObject> $attributeClass
	 * @param class-string                  $className
	 *
	 * @return AttributeObject|null
	 */
	public function generateAttribute (string $attributeClass, string $className) : ?object
	{
		try
		{
			$reflectionClass = new \ReflectionClass($className);
			$reflectionAttribute = $reflectionClass->getAttributes($attributeClass)[0] ?? null;

			if (null === $reflectionAttribute)
			{
				return null;
			}

			$attribute = $reflectionAttribute->newInstance();
			\assert(\is_a($attribute, $attributeClass));

			return $attribute;
		}
		catch (\ReflectionException)
		{
			return null;
		}
	}
}
