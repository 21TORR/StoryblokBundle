<?php declare(strict_types=1);

namespace Torr\Storyblok\Definition;

use Torr\Storyblok\Definition\Data\ComponentDefinition;
use Torr\Storyblok\Definition\Data\EmbedDefinition;
use Torr\Storyblok\Definition\Loader\ComponentDefinitionLoader;
use Torr\Storyblok\Story\Data\Block;
use Torr\Storyblok\Story\Data\Story;

/**
 * @final
 */
class DefinitionRegistry
{
	private array $byStoryClass = [];
	private array $byKey = [];
	private array $embeddedRegistry = [];

	/**
	 */
	public function __construct (
		private readonly ComponentDefinitionLoader $definitionLoader,
	) {}

	/**
	 * @param class-string<Block|Story> $storyClass
	 *
	 * @return $this
	 */
	public function register (string $storyClass) : self
	{
		if (\array_key_exists($storyClass, $this->byStoryClass))
		{
			return $this;
		}

		$definition = $this->definitionLoader->loadDefinition($this, $storyClass);
		$this->byKey[$definition->key] = $definition;
		$this->byStoryClass[$definition->storyClass] = $definition;

		return $this;
	}

	/**
	 * @return $this
	 */
	public function registerEmbedded (string $embeddedClass) : self
	{
		if (\array_key_exists($embeddedClass, $this->embeddedRegistry))
		{
			return $this;
		}

		$definition = $this->definitionLoader->loadEmbedDefinition($this, $embeddedClass);
		$this->embeddedRegistry[$definition->embeddedClass] = $definition;

		return $this;
	}

	/**
	 */
	public function getByKey (string $key) : ?ComponentDefinition
	{
		return $this->byKey[$key] ?? null;
	}

	/**
	 */
	public function getByStoryClass (string $storyClass) : ?ComponentDefinition
	{
		return $this->byStoryClass[$storyClass] ?? null;
	}

	/**
	 *
	 */
	public function getEmbeddedDefinition (string $embeddedClass) : ?EmbedDefinition
	{
		return $this->embeddedRegistry[$embeddedClass] ?? null;
	}
}
