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
		$keys = $componentManager->getComponentKeysForFilter($this->filter);

		$result = [
			$this->componentsConfigKey => $keys,
		];

		if (null !== $this->enableConfigKey)
		{
			$result[$this->enableConfigKey] = !empty($keys);
		}

		return $result;
	}
}
