<?php declare(strict_types=1);

namespace Torr\Storyblok\Definition\Loader;

use Torr\Storyblok\Component\Config\ComponentType;
use Torr\Storyblok\Definition\Data\ComponentDefinition;
use Torr\Storyblok\Definition\Data\EmbedDefinition;
use Torr\Storyblok\Definition\Registry\DefinitionRegistry;
use Torr\Storyblok\Definition\Exception\ComponentDefinitionException;
use Torr\Storyblok\Definition\Exception\DuplicateFieldDefinitionException;
use Torr\Storyblok\Definition\Exception\InvalidComponentDefinitionException;
use Torr\Storyblok\Definition\Mapping\Component;
use Torr\Storyblok\Story\Data\Block;
use Torr\Storyblok\Story\Data\Story;

/**
 * @final
 */
readonly class ComponentDefinitionLoader
{
	/**
	 */
	public function __construct (
		private FieldDefinitionLoader $fieldDefinitionLoader = new FieldDefinitionLoader(),
	) {}

	/**
	 * @throws ComponentDefinitionException
	 */
	public function loadDefinition (
		DefinitionRegistry $registry,
		string $storyClass,
	) : ?ComponentDefinition
	{
		try
		{
			$reflectionClass = new \ReflectionClass($storyClass);
		}
		catch (\ReflectionException $exception)
		{
			throw new InvalidComponentDefinitionException(
				message: \sprintf(
					"Could not load reflection information for class '%s': %s",
					$storyClass,
					$exception->getMessage()
				),
				previous: $exception,
			);
		}

		$component = $this->loadAttribute($reflectionClass, Component::class);

		if (null === $component)
		{
			if (is_a($storyClass, Block::class, true))
			{
				throw new InvalidComponentDefinitionException(\sprintf(
					"Block component '%s' must have attribute '%s'",
					$storyClass,
					Component::class,
				));
			}

			if (is_a($storyClass, Story::class, true))
			{
				throw new InvalidComponentDefinitionException(\sprintf(
					"Story component '%s' must have attribute '%s'",
					$storyClass,
					Component::class,
				));
			}

			return null;
		}

		$componentType = match (true)
		{
			is_a($storyClass, Story::class, true) => ComponentType::Standalone,
			is_a($storyClass, Block::class, true) => ComponentType::Nested,
			default => throw new InvalidComponentDefinitionException(\sprintf(
				"Storyblok component '%s' must either extend '%s' or '%s'",
				$storyClass,
				Story::class,
				Block::class,
			)),
		};

		return new ComponentDefinition(
			storyClass: $storyClass,
			key: $component->key,
			label: $component->label,
			type: $componentType,
			fields: $this->loadFields($registry, $reflectionClass),
			description: $component->description,
			tags: array_map($this->resolveEnum(...), $component->tags),
			folder: $this->resolveEnum($component->folder),
		);
	}

	/**
	 */
	public function loadEmbedDefinition (DefinitionRegistry $registry, string $embedClass) : EmbedDefinition
	{
		return new EmbedDefinition(
			embeddedClass: $embedClass,
			label: "bah",
			key: $embedClass,
			fields: $this->loadFields($registry, new \ReflectionClass($embedClass)),
		);
	}

	/**
	 *
	 */
	private function loadFields (
		DefinitionRegistry $registry,
		\ReflectionClass $class,
	) : array
	{
		$fields = [];

		foreach ($class->getProperties() as $property)
		{
			foreach ($this->fieldDefinitionLoader->loadFieldDefinitions($registry, $property) as $key => $definition)
			{
				if (\array_key_exists($key, $fields))
				{
					throw new DuplicateFieldDefinitionException(\sprintf(
						"Class '%s' has multiple fields with the same key '%s'",
						$class->getName(),
						$key,
					));
				}

				$fields[$key] = $definition;
			}
		}

		return $fields;
	}

	/**
	 * @template AttributeType of object
	 *
	 * @param class-string<AttributeType> $attribute
	 *
	 * @return AttributeType|null
	 */
	private function loadAttribute (\ReflectionClass $storyClass, string $attribute) : ?object
	{
		return $storyClass->getAttributes($attribute)[0]?->newInstance() ?? null;
	}

	/**
	 * @phpstan-return $value is null ? null : string
	 */
	private function resolveEnum (string|\BackedEnum|null $value) : ?string
	{
		return $value instanceof \BackedEnum
			? $value->value
			: $value;
	}
}
