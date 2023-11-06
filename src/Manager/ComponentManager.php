<?php declare(strict_types=1);

namespace Torr\Storyblok\Manager;

use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Contracts\Cache\CacheInterface;
use Torr\Storyblok\Cache\DebugCache;
use Torr\Storyblok\Cache\DebugCacheFactory;
use Torr\Storyblok\Component\AbstractComponent;
use Torr\Storyblok\Definition\Component\ComponentDefinition;
use Torr\Storyblok\Definition\Component\ComponentDefinitionFactory;
use Torr\Storyblok\Definition\Component\ComponentDefinitionRegistry;
use Torr\Storyblok\Exception\Component\InvalidComponentDefinitionException;
use Torr\Storyblok\Exception\Component\UnknownComponentKeyException;
use Torr\Storyblok\Exception\Component\UnknownStoryTypeException;
use Torr\Storyblok\Mapping\Storyblok;
use Torr\Storyblok\Story\StoryDocument;

/**
 * @final
 */
class ComponentManager
{
	private const CACHE_KEY = "storyblok.definitions";

	/** @var DebugCache<ComponentDefinitionRegistry> */
	private DebugCache $definitionCache;

	/**
	 */
	public function __construct (
		/** @var array<string, class-string> */
		array $storyComponents,
		ComponentDefinitionFactory $definitionFactory,
		DebugCacheFactory $debugCacheFactory,
	)
	{
		$this->definitionCache = $debugCacheFactory->createCache(
			self::CACHE_KEY,
			fn () => $definitionFactory->generateAllDefinitions($storyComponents),
		);
	}




	/**
	 * @return array<AbstractComponent>
	 */
	public function getAllComponents () : array
	{
		$components = \array_map(
			fn (string $key) => $this->getComponent($key),
			\array_keys($this->components->getProvidedServices()),
		);

		\usort(
			$components,
			static fn (AbstractComponent $left, AbstractComponent $right) => \strnatcmp($left->getDisplayName(), $right->getDisplayName()),
		);

		return $components;
	}

	/**
	 * Returns the first component that creates a story of the given type
	 *
	 * @template TStory of StoryDocument
	 *
	 * @param class-string<TStory> $storyType
	 *
	 * @return AbstractComponent<TStory>
	 *@throws UnknownStoryTypeException
	 *
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
	 *
	 * @param array<string|\BackedEnum> $tags
	 *
	 * @return string[]
	 */
	public function getComponentKeysForTags (array $tags) : array
	{
		$matches = [];

		$normalizeTag = static fn (string|\BackedEnum $tag) => $tag instanceof \BackedEnum
			? $tag->value
			: $tag;

		$normalizedTags = \array_map($normalizeTag, $tags);

		foreach ($this->getAllComponents() as $component)
		{
			$componentTags = \array_map($normalizeTag, $component->getTags());

			if (!empty(\array_intersect($normalizedTags, $componentTags)))
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
			$component = $this->components->get($key);
			\assert($component instanceof AbstractComponent);

			return $component;
		}
		catch (ServiceNotFoundException $exception)
		{
			throw new UnknownComponentKeyException(
				message: \sprintf(
					"Unknown component type: %s",
					$key,
				),
				componentKey: $key,
				previous: $exception,
			);
		}
	}

	/**
	 */
	public function getDefinitions () : ComponentDefinitionRegistry
	{
		return $this->definitionCache->get();
	}

	/**
	 * Returns the definition of the component
	 */
	public function getDefinition (string $key) : ComponentDefinition
	{
		return $this->getDefinitions()->get($key);
	}
}
