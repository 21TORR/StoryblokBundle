<?php declare(strict_types=1);

namespace Torr\Storyblok\Manager\Sync;

use Torr\Cli\Console\Style\TorrStyle;
use Torr\Storyblok\Api\Data\ComponentImport;
use Torr\Storyblok\Api\ManagementApi;
use Torr\Storyblok\Exception\Api\ApiRequestException;
use Torr\Storyblok\Exception\InvalidComponentConfigurationException;
use Torr\Storyblok\Exception\Sync\SyncFailedException;
use Torr\Storyblok\Manager\Normalizer\ComponentNormalizer;

final class ComponentSync
{
	/**
	 */
	public function __construct (
		private readonly ManagementApi $managementApi,
		private readonly ComponentNormalizer $componentNormalizer,
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
			$normalized = $this->componentNormalizer->normalize($io);

			if (!$sync)
			{
				return;
			}

			$this->syncComponents($io, $normalized);
		}
		catch (InvalidComponentConfigurationException|ApiRequestException $exception)
		{
			throw new SyncFailedException($exception->getMessage(), previous: $exception);
		}
	}


	/**
	 * Syncs all components
	 *
	 * @param ComponentImport[] $normalizedComponents
	 */
	private function syncComponents (
		TorrStyle $io,
		array $normalizedComponents,
	) : void
	{
		foreach ($normalizedComponents as $key => $config)
		{
			$io->write("Syncing {$key} ");
			$performedAction = $this->managementApi->syncComponent($config->config, $config->groupLabel);
			$io->writeln(\sprintf("%s <fg=green>✓</>", $performedAction->value));
		}
	}
}
