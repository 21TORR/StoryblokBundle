<?php declare(strict_types=1);

namespace Torr\Storyblok\Definition;

use Torr\Storyblok\Definition\Data\ComponentDefinition;

/**
 * @final
 */
class DefinitionRegistry
{
	private array $byStoryClass = [];
	private array $byKey = [];

	/**
	 * @param iterable<ComponentDefinition> $definitions
	 */
	public function __construct (
		iterable $definitions = [],
	)
	{
		foreach ($definitions as $definition)
		{
			$this->byKey[$definition->key] = $definition;
			$this->byKey[$definition->storyClass] = $definition;
		}
	}

	/**
	 * @return $this
	 */
	public function register (ComponentDefinition $definition) : self
	{
		$this->byKey[$definition->key] = $definition;
		$this->byStoryClass[$definition->storyClass] = $definition;

		return $this;
	}

	/**
	 */
	public function getByStoryClass (string $storyClass) : ?ComponentDefinition
	{
		return $this->byStoryClass[$storyClass] ?? null;
	}

	/**
	 */
	public function getByKey (string $key) : ?ComponentDefinition
	{
		return $this->byKey[$key] ?? null;
	}
}
