<?php declare(strict_types=1);

namespace Torr\Storyblok\Manager\Sync\Filter;

use Torr\Storyblok\Component\Filter\ComponentFilter;
use Torr\Storyblok\Manager\ComponentManager;

/**
 * @internal used to wrap the filter with the names it should be transformed to
 */
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
		private readonly ?string $enableConfigKey = null,
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

		if (!empty($this->filter->tags))
		{
			foreach ($componentManager->getComponentKeysForTags($this->filter->tags) as $componentKey)
			{
				$result[$componentKey] = true;
			}
		}

		return \array_keys($result);
	}
}
