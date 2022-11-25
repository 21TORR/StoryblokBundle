<?php declare(strict_types=1);

namespace Torr\Storyblok\Story;

use Torr\Storyblok\Context\ComponentContext;
use Torr\Storyblok\Exception\Component\UnknownComponentKeyException;
use Torr\Storyblok\Exception\Story\ComponentWithoutStoryException;
use Torr\Storyblok\Exception\Story\StoryHydrationFailed;
use Torr\Storyblok\Manager\ComponentManager;

final class StoryFactory
{
	/**
	 */
	public function __construct (
		private readonly ComponentManager $componentManager,
		private readonly ComponentContext $storyblokContext,
	) {}

	/**
	 * Creates a story with the given data
	 *
	 * @throws StoryHydrationFailed
	 */
	public function createFromApiData (array $data) : Story
	{
		$type = $data["content"]["component"] ?? null;

		if (!\is_string($type))
		{
			throw new StoryHydrationFailed(\sprintf(
				"Could not hydrate story %s: no component type given",
				$data["id"] ?? "n/a",
			));
		}

		try
		{
			$component = $this->componentManager->getComponent($type);
			$storyClass = $component->getStoryClass();

			if (null === $storyClass)
			{
				throw new ComponentWithoutStoryException(\sprintf(
					"Can't create story for component of type '%s', as no story class was defined.",
					$component::getKey(),
				));
			}

			if (!\is_a($storyClass, Story::class, true))
			{
				throw new StoryHydrationFailed(\sprintf(
					"Could not hydrate story of type '%s': story class does not extend %s",
					$component::getKey(),
					Story::class,
				));
			}

			$story = new $storyClass($data, $component, $this->storyblokContext);
			$story->validate($this->storyblokContext);

			return $story;
		}
		catch (UnknownComponentKeyException $exception)
		{
			throw new StoryHydrationFailed(\sprintf(
				"Could not hydrate story %s of type %s: %s",
				$data["id"] ?? "n/a",
				$type,
				$exception->getMessage(),
			), previous: $exception);
		}
	}
}
