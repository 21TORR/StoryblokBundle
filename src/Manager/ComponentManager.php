<?php declare(strict_types=1);

namespace Torr\Storyblok\Manager;

use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Torr\Storyblok\Component\AbstractComponent;
use Torr\Storyblok\Exception\Component\UnknownComponentKeyException;
use Torr\Storyblok\Exception\Component\UnknownStoryTypeException;
use Torr\Storyblok\Story\Story;

final class ComponentManager
{
	/**
	 */
	public function __construct (
		private readonly ServiceLocator $components,
	) {}

	/**
	 * @return array<AbstractComponent>
	 */
	public function getAllComponents () : array
	{
		return \array_map(
			fn (string $key) => $this->components->get($key),
			\array_keys($this->components->getProvidedServices()),
		);
	}

	/**
	 * Returns the first component that creates a story of the given type
	 *
	 * @template TStory of Story
	 *
	 * @param class-string<TStory> $storyType
	 *
	 * @throws UnknownStoryTypeException
	 *
	 * @return AbstractComponent<TStory>
	 */
	public function getComponentByStoryType (string $storyType) : AbstractComponent
	{
		foreach ($this->getAllComponents() as $component)
		{
			if ($component->getStoryClass() === $storyType)
			{
				return $component;
			}
		}

		throw new UnknownStoryTypeException(\sprintf(
			"Found no component generating a story of type '%s'",
			$storyType,
		));
	}


	/**
	 * Returns the component keys for all components with any of the given tags
	 */
	public function getComponentKeysForTags (array $tags) : array
	{
		$matches = [];

		foreach ($this->getAllComponents() as $component)
		{
			$componentTags = $component->getTags();

			if (!empty(\array_intersect($tags, $componentTags)))
			{
				$matches[] = $component::getKey();
			}
		}

		return $matches;
	}


	/**
	 * Gets the component by key
	 *
	 * @throws UnknownComponentKeyException
	 */
	public function getComponent (string $key) : AbstractComponent
	{
		try
		{
			return $this->components->get($key);
		}
		catch (ServiceNotFoundException $exception)
		{
			throw new UnknownComponentKeyException(\sprintf(
				"Unknown component type: %s",
				$key,
			), previous: $exception);
		}
	}
}
