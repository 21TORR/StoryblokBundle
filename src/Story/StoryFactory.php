<?php declare(strict_types=1);

namespace Torr\Storyblok\Story;

use Psr\Log\LoggerInterface;
use Torr\Storyblok\Config\StoryblokConfig;
use Torr\Storyblok\Context\ComponentContext;
use Torr\Storyblok\Exception\Component\UnknownComponentKeyException;
use Torr\Storyblok\Exception\Story\ComponentWithoutStoryException;
use Torr\Storyblok\Exception\Story\InvalidDataException;
use Torr\Storyblok\Exception\Story\StoryHydrationFailed;
use Torr\Storyblok\Manager\ComponentManager;

final class StoryFactory
{
	/**
	 */
	public function __construct (
		private readonly ComponentManager $componentManager,
		private readonly ComponentContext $storyblokContext,
		private readonly StoryblokConfig $config,
		private readonly LoggerInterface $logger,
	) {}

	/**
	 * Creates a story with the given data
	 *
	 * @throws StoryHydrationFailed
	 */
	public function createFromApiData (array $data) : ?Story
	{
		$type = $data["content"]["component"] ?? null;

		if (!\is_string($type))
		{
			throw new StoryHydrationFailed(sprintf(
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
			$component = $this->componentManager->getComponent($type);
			$storyClass = $component->getStoryClass();

			if (null === $storyClass)
			{
				throw new ComponentWithoutStoryException(sprintf(
					"Can't create story for component of type '%s', as no story class was defined.",
					$component::getKey(),
				));
			}

			if (!is_a($storyClass, Story::class, true))
			{
				throw new StoryHydrationFailed(sprintf(
					"Could not hydrate story of type '%s': story class does not extend %s",
					$component::getKey(),
					Story::class,
				));
			}

			$data["_locale_level"] = $this->config->getLocaleLevel();

			$story = new $storyClass($data, $component, $this->storyblokContext);
			$story->validate($this->storyblokContext);

			return $story;
		}
		catch (InvalidDataException $exception)
		{
			throw new StoryHydrationFailed(sprintf(
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
	 * Checks the content of the story to see, whether the story was saved at least once
	 */
	private function isUnsavedStory (array $content) : bool
	{
		// a story was not yet saved, if we have no additional data except for the uid, component type key and the editable HTML snippet
		return 3 === \count($content) && isset($content["_uid"], $content["component"], $content["_editable"]);
	}
}
