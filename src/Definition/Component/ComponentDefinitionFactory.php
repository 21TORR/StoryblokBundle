<?php declare(strict_types=1);

namespace Torr\Storyblok\Definition\Component;

use Torr\Storyblok\Definition\Component\Reflection\ReflectionHelper;
use Torr\Storyblok\Definition\Field\FieldDefinition;
use Torr\Storyblok\Exception\Component\InvalidComponentDefinitionException;
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
	 * @return array<string, FieldDefinition>
	 */
	private function createFieldDefinitions (\ReflectionClass $class) : array
	{
		$definitions = [];

		foreach ($class->getProperties() as $reflectionProperty)
		{
			if ($reflectionProperty->isStatic())
			{
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
}
