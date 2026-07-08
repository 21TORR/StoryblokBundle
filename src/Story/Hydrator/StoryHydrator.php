<?php declare(strict_types=1);

namespace Torr\Storyblok\Story\Hydrator;

use Symfony\Component\PropertyAccess\Exception\AccessException;
use Symfony\Component\PropertyAccess\Exception\InvalidTypeException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Torr\Storyblok\Context\ComponentContext;
use Torr\Storyblok\Definition\Data\FieldDefinition;
use Torr\Storyblok\Definition\DefinitionRegistry;
use Torr\Storyblok\Story\Data\NestedStory;
use Torr\Storyblok\Story\Data\StandaloneStory;
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
	public function hydrateDocument (array $data, string $spaceId, int $localeLevel) : StandaloneStory
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

		if (!$story instanceof StandaloneStory)
		{
			throw new BrokenStoryDataException(\sprintf(
				"Tried to instantiate document story, but got '%s'",
				$definition->storyClass,
			));
		}

		\assert($story instanceof StandaloneStory);
		$story->metaData = $this->metaDataHydrator->hydrateStandaloneStoryMetaData($data, $spaceId, $localeLevel);

		foreach ($definition->fields as $field)
		{
			$this->hydrateValue($story, $field, $data["content"]);
		}

		return $story;
	}

	public function hydrateBlok (array $data) : NestedStory
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

		if (!$story instanceof NestedStory)
		{
			throw new BrokenStoryDataException(\sprintf(
				"Tried to instantiate blok story, but got '%s'",
				$definition->storyClass,
			));
		}

		\assert($story instanceof NestedStory);
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
			$transformedValue = $definition->field->transformStoryblokValue(
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
