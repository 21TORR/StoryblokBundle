<?php declare(strict_types=1);

namespace Torr\Storyblok\Definition\Registry;

use Torr\Storyblok\Definition\Data\ComponentDefinition;
use Torr\Storyblok\Definition\Exception\UnknownComponentException;

/**
 * @final
 */
class DefinitionDiscoverer
{
	public array $components = [];

	public function __construct (
		private readonly DefinitionRegistry $registry,
	) {}

	/**
	 * @throws UnknownComponentException
	 * @return list<ComponentDefinition>
	 */
	public function discoverReachableComponents (array $storyClasses) : array
	{
		$this->components = [];

		foreach ($storyClasses as $storyClass)
		{
			$definition = $this->registry->getByStoryClass($storyClass)
				?? throw new UnknownComponentException(\sprintf(
					"Can't find definition for story '%s'",
					$storyClass,
				));

			$this->discoverComponent($definition);
		}

		return \array_values($this->components);
	}

	/**
	 *
	 */
	private function discoverComponent (ComponentDefinition $definition) : void
	{
		if (\array_key_exists($definition->key, $this->components))
		{
			return;
		}

		$this->components[$definition->key] = $definition;
	}
}
