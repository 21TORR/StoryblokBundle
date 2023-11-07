<?php declare(strict_types=1);

namespace Torr\Storyblok\Hydrator;

use Psr\Log\LoggerInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Torr\Storyblok\Definition\Field\EmbeddedFieldDefinition;
use Torr\Storyblok\Definition\Field\FieldDefinition;
use Torr\Storyblok\Exception\Component\UnknownComponentKeyException;
use Torr\Storyblok\Exception\Story\InvalidDataException;
use Torr\Storyblok\Exception\Story\StoryHydrationFailed;
use Torr\Storyblok\Manager\ComponentManager;
use Torr\Storyblok\Mapping\Embed\EmbeddedStory;
use Torr\Storyblok\Mapping\Field\BloksField;
use Torr\Storyblok\Story\NestedStory;
use Torr\Storyblok\Story\StandaloneNestedStory;
use Torr\Storyblok\Story\MetaData\NestedStoryMetaData;
use Torr\Storyblok\Story\MetaData\StandaloneStoryMetaData;
use Torr\Storyblok\Data\Validator\DataValidator;

final class StoryHydrator
{
	/**
	 */
	public function __construct (
		private readonly ComponentManager $componentManager,
		private readonly LoggerInterface $logger,
		private readonly PropertyAccessorInterface $accessor,
		private readonly DataValidator $dataValidator,
	) {}

	/**
	 * Creates a story with the given data
	 *
	 * @throws StoryHydrationFailed
	 */
	public function createFromApiData (array $data) : ?object
	{
		$type = $data["content"]["component"] ?? null;

		if (!\is_string($type))
		{
			throw new StoryHydrationFailed(\sprintf(
				"Could not hydrate story %s: no component type given",
				$data["id"] ?? "n/a",
			));
		}

		// If the story was never saved, the validation rules never applied.
		// So we just skip the whole story completely.
		if ($this->isUnsavedStory($data["content"]))
		{
			$this->logger->warning("Skipping unsaved story {id} of type {type}", [
				"id" => $data["id"] ?? "n/a",
				"type" => $type,
			]);

			return null;
		}

		try
		{
			return $this->hydrateDocument($type, $data);
		}
		catch (InvalidDataException $exception)
		{
			throw new StoryHydrationFailed(\sprintf(
				"Failed to hydrate story (Id: '%s', Name: '%s') of type '%s' due to invalid data: %s",
				$data["id"] ?? "n/a",
				$data["name"] ?? "n/a",
				$type,
				$exception->getMessage(),
			), previous: $exception);
		}
		catch (UnknownComponentKeyException $exception)
		{
			$this->logger->warning("Could not hydrate story {id} of type {type}: {message}", [
				"id" => $data["id"] ?? "n/a",
				"type" => $type,
				"message" => $exception->getMessage(),
				"exception" => $exception,
			]);

			return null;
		}
	}

	/**
	 * Hydrates the document with the data
	 */
	public function hydrateDocument (
		string $type,
		array $data,
	) : StandaloneNestedStory
	{
		$definition = $this->componentManager->getDefinition($type);
		$storyClass = $definition->storyClass;

		\assert(\is_a($storyClass, StandaloneNestedStory::class, true));

		$metaData = new StandaloneStoryMetaData($data, $type);
		$document = new $storyClass($metaData);

		return $this->mapDataToFields(
			[\sprintf("%s (%s)", $storyClass, $type)],
			$document,
			$definition->fields,
			$data["content"],
		);
	}

	/**
	 *
	 */
	public function hydrateBlok (
		array $contentPath,
		string $type,
		array $data,
	) : NestedStory
	{
		$definition = $this->componentManager->getDefinition($type);
		$blokClass = $definition->storyClass;

		\assert(\is_a($blokClass, NestedStory::class, true));

		$metaData = new NestedStoryMetaData(
			$data["_uid"],
			$data["component"],
			$data["_editable"],
		);
		$blok = new $blokClass($metaData);

		return $this->mapDataToFields(
			[...$contentPath, \sprintf("%s (%s)", $blokClass, $type)],
			$blok,
			$definition->fields,
			$data,
		);
	}


	/**
	 * @template ContentElement of object
	 *
	 * @param ContentElement $item
	 * @param array<FieldDefinition|EmbeddedFieldDefinition> $fields
	 * @return ContentElement
	 */
	private function mapDataToFields (
		array $contentPath,
		object $item,
		array $fields,
		array $completeData,
	) : object
	{
		foreach ($fields as $fieldKey => $field)
		{
			if ($field instanceof EmbeddedFieldDefinition)
			{
				$embed = new ($field->embedClass)();
				$transformed = $this->mapDataToFields(
					[...$contentPath, $field->property],
					$embed,
					$field->fields,
					$completeData,
				);
			}
			else
			{
				$data = $completeData[$fieldKey] ?? null;

				// validate data
				$field->validateData(
					$contentPath,
					$this->dataValidator,
					$data,
				);

				$transformed = $field->field->transformRawData($contentPath, $data, $this);
			}

			// map data
			$this->accessor->setValue(
				$item,
				$field->property,
				$transformed,
			);
		}

		return $item;
	}


	/**
	 * Checks the content of the story to see, whether the story was saved at least once
	 */
	private function isUnsavedStory (array $content) : bool
	{
		// a story was not yet saved, if we have no additional data except for the uid, component type key and the editable HTML snippet
		return 3 === \count($content) && isset($content["_uid"], $content["component"], $content["_editable"]);
	}
}
