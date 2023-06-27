<?php declare(strict_types=1);

namespace Torr\Storyblok\Manager\Sync\Data;

use Torr\Storyblok\Component\Config\ComponentFilter;
use Torr\Storyblok\Manager\ComponentManager;

final class ResolvableComponentFilter
{
	/**
	 */
	public function __construct (
		private readonly ComponentFilter $filter,
		/**
		 * The key in the management api array where the keys are defined
		 */
		private readonly string $componentsConfigKey,
		/**
		 * The optional key in the management api array where it is configured whether the filter
		 * should be applied.
		 */
		private readonly ?string $enableConfigKey,
	) {}

	/**
	 *
	 */
	public function transformToManagementApiData (ComponentManager $componentManager) : array
	{
		$keys = $this->resolveComponentKeys($componentManager);

		$result = [
			$this->componentsConfigKey => $keys,
		];

		if (null !== $this->enableConfigKey)
		{
			$result[$this->enableConfigKey] = !empty($keys);
		}

		return $result;
	}

	/**
	 * Transforms the filter to the component keys
	 */
	private function resolveComponentKeys (ComponentManager $componentManager) : array
	{
		$result = [];

		foreach ($this->filter->components as $component)
		{
			if ($component instanceof \BackedEnum)
			{
				$result[$component->value] = true;
			}
			else
			{
				$result[$component] = true;
			}
		}

		foreach ($componentManager->getComponentKeysForTags($this->filter->tags) as $componentKey)
		{
			$result[$componentKey] = true;
		}

		return \array_keys($result);
	}
}
