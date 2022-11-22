<?php declare(strict_types=1);

namespace Torr\Storyblok\Manager;

use Symfony\Component\DependencyInjection\ServiceLocator;
use Torr\Storyblok\Component\AbstractComponent;

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
}
