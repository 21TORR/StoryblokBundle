<?php declare(strict_types=1);

namespace Torr\Storyblok\Definition\Component;

use Torr\Storyblok\Definition\Component\Reflection\ReflectionHelper;
use Torr\Storyblok\Definition\Field\EmbeddedFieldDefinition;
use Torr\Storyblok\Definition\Field\FieldDefinition;
use Torr\Storyblok\Exception\Component\InvalidComponentDefinitionException;
use Torr\Storyblok\Mapping\Embed\EmbeddedStory;
use Torr\Storyblok\Mapping\Field\AbstractField;
use Torr\Storyblok\Mapping\FieldAttribute\FieldAttributeInterface;
use Torr\Storyblok\Mapping\Storyblok;

final readonly class ComponentDefinitionFactory
{
	/**
	 */
	public function __construct (
		private ReflectionHelper $helper,
	) {}


	/**
	 *
	 */
	public function generateAllDefinitions (
		array $classMappings,
	) : ComponentDefinitionRegistry
	{
		$result = [];

		foreach ($classMappings as $key => $storyblokClass)
		{
			$result[$key] = $this->createDefinition($storyblokClass);
		}

		return new ComponentDefinitionRegistry($result);
	}


	/**
	 * @param class-string $storyblokClass
	 */
	private function createDefinition (
		string $storyblokClass,
	) : ComponentDefinition
	{
		try
		{
			$reflectionClass = new \ReflectionClass($storyblokClass);
			$blok = $this->helper->getRequiredSingleAttribute($reflectionClass, Storyblok::class);

			return new ComponentDefinition(
				$blok,
				$storyblokClass,
				$this->createFieldDefinitions($reflectionClass),
			);
		}
		catch (\ReflectionException $exception)
		{
			throw new InvalidComponentDefinitionException(
				message: \sprintf(
					"Invalid component definition: %s",
					$exception->getMessage(),
				),
				previous: $exception,
			);
		}
	}

	/**
	 * @return array<string, FieldDefinition|EmbeddedFieldDefinition>
	 */
	private function createFieldDefinitions (
		\ReflectionClass $class,
		bool $allowEmbeds = true,
	) : array
	{
		$definitions = [];

		foreach ($class->getProperties() as $reflectionProperty)
		{
			if ($reflectionProperty->isStatic())
			{
				continue;
			}

			// check for embeds
			$embed = $this->helper->getOptionalSingleAttribute($reflectionProperty, EmbeddedStory::class);

			if (null !== $embed)
			{
				if (!$allowEmbeds)
				{
					throw new InvalidComponentDefinitionException(\sprintf(
						"Can't use embedded field in embedded type at '%s'",
						$this->formatPropertyName($reflectionProperty),
					));
				}

				$embedClass = $this->getSingleClassType($reflectionProperty);
				$definitions[$embed->prefix] = new EmbeddedFieldDefinition(
					definition: $embed,
					property: $reflectionProperty->getName(),
					embedClass: $embedClass->getName(),
					fields: $this->createFieldDefinitions($embedClass, allowEmbeds: false),
				);
				continue;
			}

			$fieldDefinition = $this->helper->getOptionalSingleAttribute($reflectionProperty, AbstractField::class);

			if (null === $fieldDefinition)
			{
				continue;
			}

			$definitions[$fieldDefinition->key] = new FieldDefinition(
				$fieldDefinition,
				$reflectionProperty->getName(),
				$this->helper->getAttributes($reflectionProperty, FieldAttributeInterface::class),
			);
		}

		return $definitions;
	}

	/**
	 */
	private function getSingleClassType (\ReflectionProperty $property) : \ReflectionClass
	{
		$type = $property->getType();

		if (!$type instanceof \ReflectionNamedType)
		{
			throw new InvalidComponentDefinitionException(
				message: \sprintf(
					"Can't use non-singular object type on embedded field: %s",
					$this->formatPropertyName($property),
				),
			);
		}

		try
		{
			return new \ReflectionClass($type->getName());
		}
		catch (\ReflectionException $exception)
		{
			throw new InvalidComponentDefinitionException(
				message: \sprintf(
					"Invalid type for embedded field '%s': %s",
					$this->formatPropertyName($property),
					$exception->getMessage(),
				),
				previous: $exception,
			);
		}
	}


	/**
	 *
	 */
	private function formatPropertyName (\ReflectionProperty $property) : string
	{
		return \sprintf(
			"%s::$%s",
			$property->getDeclaringClass()->getName(),
			$property->getName(),
		);
	}
}
