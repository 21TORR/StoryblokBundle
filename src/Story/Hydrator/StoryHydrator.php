<?php declare(strict_types=1);

namespace Torr\Storyblok\Story\Hydrator;

use Symfony\Component\PropertyAccess\Exception\AccessException;
use Symfony\Component\PropertyAccess\Exception\InvalidTypeException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Torr\Storyblok\Context\ComponentContext;
use Torr\Storyblok\Definition\Data\FieldDefinition;
use Torr\Storyblok\Definition\Registry\DefinitionRegistry;
use Torr\Storyblok\Story\Data\Block;
use Torr\Storyblok\Story\Data\Story;
use Torr\Storyblok\Story\Exception\BrokenStoryDataException;
use Torr\Storyblok\Story\Exception\InaccessiblePropertyException;
use Torr\Storyblok\Story\Exception\UnknownComponentException;
use Torr\Storyblok\Story\Exception\UnknownEmbedException;

/**
 * @final
 */
readonly class StoryHydrator
{
	/**
	 */
	public function __construct (
		private DefinitionRegistry $definitionRegistry,
		private PropertyAccessorInterface $accessor,
		private ComponentContext $componentContext,
		private MetaDataHydrator $metaDataHydrator,
	) {}

	/**
	 *
	 */
	public function hydrateDocument (array $data, string $spaceId, int $localeLevel) : Story
	{
		$type = $data["content"]["component"] ?? null;

		if (!\is_string($type))
		{
			throw new BrokenStoryDataException("Could not hydrate document story: missing component type");
		}

		$definition = $this->definitionRegistry->getByKey($type);

		if (null === $definition)
		{
			throw new UnknownComponentException(\sprintf(
				"Could not hydrate story: unknown component '%s'",
				$type,
			));
		}

		$story = new \ReflectionClass($definition->storyClass)->newInstance();

		if (!$story instanceof Story)
		{
			throw new BrokenStoryDataException(\sprintf(
				"Tried to instantiate document story, but got '%s'",
				$definition->storyClass,
			));
		}

		\assert($story instanceof Story);
		$story->metaData = $this->metaDataHydrator->hydrateStandaloneStoryMetaData($data, $spaceId, $localeLevel);

		foreach ($definition->fields as $field)
		{
			$this->hydrateValue($story, $field, $data["content"]);
		}

		return $story;
	}

	public function hydrateBlok (array $data) : Block
	{
		$type = $data["component"] ?? null;

		if (!\is_string($type))
		{
			throw new BrokenStoryDataException("Could not hydrate blok story: missing component type");
		}

		$definition = $this->definitionRegistry->getByKey($type);

		if (null === $definition)
		{
			throw new UnknownComponentException(\sprintf(
				"Could not hydrate story: unknown component '%s'",
				$type,
			));
		}

		$story = new \ReflectionClass($definition->storyClass)->newInstance();

		if (!$story instanceof Block)
		{
			throw new BrokenStoryDataException(\sprintf(
				"Tried to instantiate blok story, but got '%s'",
				$definition->storyClass,
			));
		}

		\assert($story instanceof Block);
		$story->metaData = $this->metaDataHydrator->hydrateNestedStoryMetaData($data);

		foreach ($definition->fields as $field)
		{
			$this->hydrateValue($story, $field, $data);
		}

		return $story;
	}

	public function hydrateEmbed (string $embedClass, string $contentPathPrefix, array $data) : object
	{
		$definition = $this->definitionRegistry->getEmbeddedDefinition($embedClass);

		if (null === $definition)
		{
			throw new UnknownEmbedException(\sprintf(
				"Could not hydrate story: unknown component '%s'",
				$embedClass,
			));
		}

		$story = new \ReflectionClass($embedClass)->newInstance();

		foreach ($definition->fields as $field)
		{
			$this->hydrateValue($story, $field, $data, $contentPathPrefix);
		}

		return $story;
	}

	/**
	 */
	private function hydrateValue (
		object $story,
		FieldDefinition $definition,
		array $data,
		string $contentPathPrefix = "",
	) : void
	{
		try
		{
			$transformedValue = $definition->mapping->transformStoryblokValue(
				contentPath: $contentPathPrefix . $definition->key,
				storyData: $data,
				definition: $definition,
				context: $this->componentContext,
				hydrator: $this,
			);

			$this->accessor->setValue($story, $definition->propertyPath, $transformedValue);
		}
		catch (AccessException $exception)
		{
			throw new InaccessiblePropertyException(
				message: \sprintf(
					"Can't find a way to hydrate value of property '%s::\$%s'",
					$story::class,
					$definition->propertyPath,
				),
				previous: $exception,
			);
		}
		catch (InvalidTypeException $exception)
		{
			throw new InaccessiblePropertyException(
				message: \sprintf(
					"Can't find a way to hydrate value of property '%s::\$%s': %s",
					$story::class,
					$definition->propertyPath,
					$exception->getMessage(),
				),
				previous: $exception,
			);
		}
	}
}
