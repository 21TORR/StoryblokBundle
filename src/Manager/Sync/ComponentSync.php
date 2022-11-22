<?php declare(strict_types=1);

namespace Torr\Storyblok\Manager\Sync;

use Torr\Cli\Console\Style\TorrStyle;
use Torr\Storyblok\Api\ManagementApi;
use Torr\Storyblok\Exception\Api\ApiRequestFailedException;
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
	) {}

	/**
	 * @throws SyncFailedException
	 */
	public function syncDefinitions (TorrStyle $io) : void
	{
		try
		{
			$io->section("Sync Component Groups");
			$io->section("Sync Components");
			$this->syncComponents($io);
		}
		catch (InvalidComponentConfigurationException|ApiRequestFailedException $exception)
		{
			throw new SyncFailedException($exception->getMessage(), previous: $exception);
		}
	}

	/**
	 * Syncs all components
	 */
	private function syncComponents (TorrStyle $io) : void
	{
		$normalized = [];

		foreach ($this->componentManager->getAllComponents() as $component)
		{
			$key = \sprintf(
				"<fg=blue>%s</> (<fg=yellow>%s</>) ... ",
				$component->getDisplayName(),
				$component::getKey(),
			);

			$io->write("Normalizing {$key} ");

			$normalized[$key] = $component->toManagementApiData();

			$io->writeln("done <fg=green>✓</>");
		}

		foreach ($normalized as $key => $config)
		{
			$io->write("Syncing {$key} ");
			$performedAction = $this->managementApi->syncComponent($config);
			$io->writeln(\sprintf("%s <fg=green>✓</>", $performedAction->value));
		}
	}
}
