<?php declare(strict_types=1);

namespace Torr\Storyblok\Manager\Sync;

use Torr\Cli\Console\Style\TorrStyle;
use Torr\Storyblok\Api\Data\ComponentImport;
use Torr\Storyblok\Api\ManagementApi;
use Torr\Storyblok\Component\Reference\ComponentsWithTags;
use Torr\Storyblok\Exception\Api\ApiRequestException;
use Torr\Storyblok\Exception\InvalidComponentConfigurationException;
use Torr\Storyblok\Exception\Sync\SyncFailedException;
use Torr\Storyblok\Manager\ComponentManager;
use Torr\Storyblok\Manager\Sync\Data\ResolvableComponentFilter;

final class ComponentSync
{
	/**
	 */
	public function __construct (
		private readonly ComponentManager $componentManager,
		private readonly ManagementApi $managementApi,
	) {}

	/**
	 * @param bool $sync Whether the data should actually be synced to Storyblok (instead of just checking whether all could be normalized).
	 *
	 * @throws SyncFailedException
	 */
	public function syncDefinitions (
		TorrStyle $io,
		bool $sync = false,
	) : void
	{
		try
		{
			$this->syncComponents($io, $sync);
		}
		catch (InvalidComponentConfigurationException|ApiRequestException $exception)
		{
			throw new SyncFailedException($exception->getMessage(), previous: $exception);
		}
	}

	/**
	 * Syncs all components
	 */
	private function syncComponents (
		TorrStyle $io,
		bool $sync = false,
	) : void
	{
		$normalized = [];

		// first: normalize everything to check if normalization fails
		foreach ($this->componentManager->getAllComponents() as $component)
		{
			$key = \sprintf(
				"<fg=blue>%s</> (<fg=yellow>%s</>) ... ",
				$component->getDisplayName(),
				$component::getKey(),
			);

			$io->write("Normalizing {$key} ");

			$normalized[$key] = new ComponentImport(
				$this->resolveComponentConfig($component->toManagementApiData()),
				$component->getComponentGroup(),
			);

			$io->writeln("done <fg=green>✓</>");
		}

		if (!$sync)
		{
			return;
		}

		// then import
		foreach ($normalized as $key => $config)
		{
			$io->write("Syncing {$key} ");
			$performedAction = $this->managementApi->syncComponent($config->config, $config->groupLabel);
			$io->writeln(\sprintf("%s <fg=green>✓</>", $performedAction->value));
		}
	}

	/**
	 * Resolves the given component config.
	 *
	 * This means resolving all {@see ComponentsWithTags} to their keys.
	 */
	private function resolveComponentConfig (array $config) : array
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
				\is_scalar($value) => $value,
				default => throw new SyncFailedException(\sprintf(
					"Invalid config value encountered: %s",
					\get_debug_type($value),
				)),
			};
		}

		return $resolved;
	}
}
