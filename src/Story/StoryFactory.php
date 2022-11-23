<?php declare(strict_types=1);

namespace Torr\Storyblok\Story;

use Torr\Storyblok\Exception\Component\UnknownComponentKeyException;
use Torr\Storyblok\Exception\Story\StoryHydrationFailed;
use Torr\Storyblok\Manager\ComponentManager;
use Torr\Storyblok\Transformer\DataTransformer;
use Torr\Storyblok\Validator\DataValidator;

final class StoryFactory
{
	/**
	 */
	public function __construct (
		private readonly ComponentManager $componentManager,
		private readonly DataTransformer $dataTransformer,
		private readonly DataValidator $dataValidator,
	) {}

	/**
	 * Creates a story with the given data
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
			return $component->createStory($data, $this->dataTransformer, $this->dataValidator);
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
