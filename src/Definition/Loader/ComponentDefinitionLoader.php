<?php declare(strict_types=1);

namespace Torr\Storyblok\Definition\Loader;

use Torr\Storyblok\Component\Config\ComponentType;
use Torr\Storyblok\Definition\Data\ComponentDefinition;
use Torr\Storyblok\Definition\Data\EmbedDefinition;
use Torr\Storyblok\Definition\DefinitionRegistry;
use Torr\Storyblok\Definition\Exception\DuplicateFieldDefinitionException;
use Torr\Storyblok\Definition\Exception\InvalidComponentDefinitionException;
use Torr\Storyblok\Definition\Mapping\NestedBlock;
use Torr\Storyblok\Definition\Mapping\StandaloneBlock;
use Torr\Storyblok\Story\Data\NestedStory;
use Torr\Storyblok\Story\Data\StandaloneStory;

/**
 * @final
 */
readonly class ComponentDefinitionLoader
{
	/**
	 */
	public function __construct (
		private FieldDefinitionLoader $fieldDefinitionLoader,
	) {}

	/**
	 *
	 */
	public function loadDefinition (
		DefinitionRegistry $registry,
		string $storyClass,
	) : ?ComponentDefinition
	{
		$reflectionClass = new \ReflectionClass($storyClass);
		$blok = $this->loadAttribute($reflectionClass, NestedBlock::class);
		$document = $this->loadAttribute($reflectionClass, StandaloneBlock::class);

		if (null === $blok && null === $document)
		{
			if (is_a($storyClass, NestedStory::class, true))
			{
				throw new InvalidComponentDefinitionException(\sprintf(
					"Blok component '%s' must have attribute '%s'",
					$storyClass,
					NestedBlock::class,
				));
			}

			if (is_a($storyClass, StandaloneStory::class, true))
			{
				throw new InvalidComponentDefinitionException(\sprintf(
					"Document component '%s' must have attribute '%s'",
					$storyClass,
					StandaloneBlock::class,
				));
			}

			return null;
		}

		if (null !== $blok && null !== $document)
		{
			throw new InvalidComponentDefinitionException(\sprintf(
				"Class '%s' can't be both document and blok. Remove one of the attribute.",
				$storyClass,
			));
		}

		return null !== $blok
			? $this->transformBlok($registry, $reflectionClass, $blok)
			: $this->transformDocument($registry, $reflectionClass, $document);
	}

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
	private function transformBlok (DefinitionRegistry $registry, \ReflectionClass $storyClass, NestedBlock $blok) : ComponentDefinition
	{
		if (!is_a($storyClass->getName(), NestedStory::class, true))
		{
			throw new InvalidComponentDefinitionException(\sprintf(
				"Blok component '%s' must extend '%s'",
				$storyClass->getName(),
				NestedStory::class,
			));
		}

		return new ComponentDefinition(
			storyClass: $storyClass->getName(),
			key: $blok->key,
			label: $blok->label,
			type: ComponentType::Nested,
			fields: $this->loadFields($registry, $storyClass),
		);
	}

	/**
	 *
	 */
	private function transformDocument (
		DefinitionRegistry $registry,
		\ReflectionClass $storyClass,
		StandaloneBlock $document,
	) : ComponentDefinition
	{
		if (!is_a($storyClass->getName(), StandaloneStory::class, true))
		{
			throw new InvalidComponentDefinitionException(\sprintf(
				"Document component '%s' must extend '%s'",
				$storyClass->getName(),
				StandaloneStory::class,
			));
		}

		return new ComponentDefinition(
			storyClass: $storyClass->getName(),
			key: $document->key,
			label: $document->label,
			type: ComponentType::Standalone,
			fields: $this->loadFields($registry, $storyClass),
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
}
