<?php declare(strict_types=1);

namespace Torr\Storyblok\Story;

use Psr\Log\LoggerInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Torr\Storyblok\Context\ComponentContext;
use Torr\Storyblok\Definition\Field\FieldDefinition;
use Torr\Storyblok\Exception\Component\UnknownComponentKeyException;
use Torr\Storyblok\Exception\Story\InvalidDataException;
use Torr\Storyblok\Exception\Story\StoryHydrationFailed;
use Torr\Storyblok\Manager\ComponentManager;
use Torr\Storyblok\Validator\DataValidator;

final class StoryFactory
{
	/**
	 */
	public function __construct (
		private readonly ComponentManager $componentManager,
		private readonly ComponentContext $storyblokContext,
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
			$definition = $this->componentManager->getDefinition($type);
			$storyClass = $definition->storyClass;

			$metaData = new StoryMetaData($data, $type);

			return $this->mapDataToFields(
				[\sprintf("%s (%s)", $storyClass, $type)],
				new $storyClass($metaData),
				$definition->fields,
				$data["content"],
			);
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
	 * @param FieldDefinition[] $fields
	 */
	private function mapDataToFields (
		array $contentPath,
		object $story,
		array $fields,
		array $completeData,
	) : object
	{
		foreach ($fields as $field)
		{
			$data = $completeData[$field->field->key] ?? null;

			// validate data
			$field->validateData(
				$contentPath,
				$this->dataValidator,
				$data,
			);

			// map data
			$this->accessor->setValue($story, $field->property, $data);
		}

		return $story;
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
