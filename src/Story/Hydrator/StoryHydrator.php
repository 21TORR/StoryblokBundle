<?php declare(strict_types=1);

namespace Torr\Storyblok\Story\Hydrator;

use Symfony\Component\PropertyAccess\Exception\AccessException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Torr\Storyblok\Context\ComponentContext;
use Torr\Storyblok\Definition\Data\FieldDefinition;
use Torr\Storyblok\Definition\DefinitionRegistry;
use Torr\Storyblok\Story\Data\BlokStory;
use Torr\Storyblok\Story\Data\DocumentStory;
use Torr\Storyblok\Story\Exception\BrokenStoryDataException;
use Torr\Storyblok\Story\Exception\InaccessiblePropertyException;
use Torr\Storyblok\Story\Exception\UnknownComponentException;

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
	public function hydrateDocument (array $data, string $spaceId, int $localeLevel) : BlokStory|DocumentStory|null
	{
		$type = $data["content"]["component"] ?? null;

		if (!\is_string($type))
		{
			throw new BrokenStoryDataException("Could not hydrate story: missing component type");
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

		if (!$story instanceof DocumentStory)
		{
			throw new BrokenStoryDataException(\sprintf(
				"Tried to instantiate document story, but got blok story at '%s'",
				$definition->storyClass,
			));
		}

		\assert($story instanceof DocumentStory);
		$story->metaData = $this->metaDataHydrator->hydrateDocumentMetaData($data, $spaceId, $localeLevel);

		foreach ($definition->fields as $field)
		{
			$this->hydrateValue($story, $field, $data["content"]);
		}

		return $story;
	}



	/**
	 */
	private function hydrateValue (object $story, FieldDefinition $definition, array $data) : void
	{
		try
		{
			$transformedValue = $definition->transformStoryblokValue(
				$data[$definition->key] ?? null,
				$this->componentContext,
			);

			$this->accessor->setValue($story, $definition->propertyPath, $transformedValue);
		}
		catch (AccessException $exception)
		{
			throw new InaccessiblePropertyException(
				message: \sprintf(
					"Can't find a way to hydrate value of property '%s' in class '%s'",
					$definition->propertyPath,
					$story::class,
				),
				previous: $exception,
			);
		}
	}
}
