<?php declare(strict_types=1);

namespace Torr\Storyblok\Component;

use Torr\Storyblok\Manager\ComponentManager;

final readonly class ComponentDiscoverer
{
	public function __construct (
		private ComponentManager $componentManager,
	) {}

	/**
	 * @param list<class-string<AbstractComponent>> $componentKeys
	 *
	 * @return list<class-string<AbstractComponent>>
	 */
	public function discoverReachableComponents (array $componentKeys) : array
	{
		$discovered = new DiscoveredComponents();

		foreach ($componentKeys as $componentKey)
		{
			$component = $this->componentManager->getComponent($componentKey);
			$component->collectNestedComponents($this->componentManager, $discovered);
		}

		return $discovered->getAllDiscoveredComponents();
	}
}
