<?php declare(strict_types=1);

namespace Torr\Storyblok\Data;

use Symfony\Component\Routing\RouterInterface;
use Torr\Storyblok\Api\Manager\ComponentManager;

final class DataSerializationContext
{
	public function __construct (
		private readonly ComponentManager $componentManager,
		private readonly RouterInterface $router,
	) {}

	public function getComponentManager () : ComponentManager
	{
		return $this->componentManager;
	}

	public function getRouter () : RouterInterface
	{
		return $this->router;
	}
}
