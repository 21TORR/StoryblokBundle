<?php declare(strict_types=1);

namespace Torr\Storyblok\Manager\Sync;

use Torr\Storyblok\Exception\Sync\SyncFailedException;
use Torr\Storyblok\Manager\ComponentManager;
use Torr\Storyblok\Manager\Sync\Filter\ResolvableComponentFilter;

final class ComponentConfigResolver
{
	/**
	 */
	public function __construct(
		private readonly ComponentManager $componentManager,
	) {}


	/**
	 * Resolves the given component config.
	 *
	 * This means resolving all {@see ResolvableComponentFilter} to their keys.
	 */
	public function resolveComponentConfig(array $config) : array
	{
		$resolved = [];

		foreach ($config as $key => $value)
		{
			if ($value instanceof ResolvableComponentFilter)
			{
				foreach ($value->transformToManagementApiData($this->componentManager) as $nestedKey => $nestedValue)
				{
					$resolved[$nestedKey] = $nestedValue;
				}

				continue;
			}

			$resolved[$key] = match (true)
			{
				\is_array($value) => $this->resolveComponentConfig($value),
				\is_scalar($value) || null === $value => $value,
				default => throw new SyncFailedException(\sprintf(
					"Invalid config value encountered: %s",
					\get_debug_type($value),
				)),
			};
		}

		return $resolved;
	}
}
