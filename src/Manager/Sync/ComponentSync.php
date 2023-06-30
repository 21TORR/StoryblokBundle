<?php declare(strict_types=1);

namespace Torr\Storyblok\Manager\Sync;

use Torr\Cli\Console\Style\TorrStyle;
use Torr\Storyblok\Api\Data\ComponentImport;
use Torr\Storyblok\Api\ManagementApi;
use Torr\Storyblok\Exception\Api\ApiRequestException;
use Torr\Storyblok\Exception\InvalidComponentConfigurationException;
use Torr\Storyblok\Exception\Sync\SyncFailedException;
use Torr\Storyblok\Manager\ComponentManager;

final class ComponentSync
{
	/**
	 */
	public function __construct (
		private readonly ComponentManager $componentManager,
		private readonly ManagementApi $managementApi,
		private readonly ComponentConfigResolver $componentConfigResolver,
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
				$this->componentConfigResolver->resolveComponentConfig($component->toManagementApiData()),
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
}
