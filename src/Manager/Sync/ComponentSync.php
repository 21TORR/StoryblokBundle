<?php declare(strict_types=1);

namespace Torr\Storyblok\Manager\Sync;

use Torr\Cli\Console\Style\TorrStyle;
use Torr\Storyblok\Api\Data\ComponentImport;
use Torr\Storyblok\Api\ManagementApi;
use Torr\Storyblok\Exception\Api\ApiRequestException;
use Torr\Storyblok\Exception\InvalidComponentConfigurationException;
use Torr\Storyblok\Exception\Sync\SyncFailedException;
use Torr\Storyblok\Manager\Normalizer\ComponentNormalizer;
use Torr\Storyblok\Manager\Sync\Diff\ComponentConfigDiffer;

final class ComponentSync
{
	/**
	 */
	public function __construct (
		private readonly ManagementApi $managementApi,
		private readonly ComponentNormalizer $componentNormalizer,
		private readonly ComponentConfigDiffer $differ,
	) {}

	/**
	 * @return bool whether the sync was actually run
	 *
	 * @throws SyncFailedException
	 */
	public function syncDefinitionsInteractively (
		TorrStyle $io,
		bool $forceSync = false,
	) : bool
	{
		try
		{
			$definitions = $this->managementApi->fetchComponentDefinitions();
			$io->writeln("• Normalizing all components");
			$normalized = $this->componentNormalizer->normalize();
			$io->writeln("<fg=green>✓</> done");

			$toRun = [];

			foreach ($normalized as $componentImport)
			{
				$existing = $definitions[$componentImport->getName()] ?? null;

				// if it is a new component: just add
				if (null === $existing)
				{
					$this->renderComponentInfo($io, $componentImport, [
						"<fg=green>New component</>",
					]);
					$toRun[] = $componentImport;

					continue;
				}

				$diff = $this->differ->diff($existing, $componentImport->config);

				if (null === $diff)
				{
					continue;
				}

				$this->renderComponentInfo($io, $componentImport, $diff);
				$toRun[] = $componentImport;
			}

			if (empty($toRun))
			{
				$io->success("No component changed, nothing to do");

				return true;
			}

			$io->writeln(sprintf(
				"• Found <fg=blue>%d</> components to sync",
				\count($toRun),
			));

			if (!$forceSync && !$io->confirm("Should the data really be synced?", false))
			{
				$io->caution("Aborting");

				return false;
			}

			$this->syncComponents($io, $toRun);

			return true;
		}
		catch (InvalidComponentConfigurationException|ApiRequestException $exception)
		{
			throw new SyncFailedException($exception->getMessage(), previous: $exception);
		}
	}

	/**
	 */
	private function renderComponentInfo (
		TorrStyle $io,
		ComponentImport $component,
		array $lines,
	) : void
	{
		$io->writeln("┌─");
		$io->writeln(sprintf("│ %s", $component->formattedLabel));
		$io->writeln("│");

		foreach ($lines as $line)
		{
			foreach (explode("\n", $line) as $splitLine)
			{
				$io->writeln("│ {$splitLine}");
			}
		}

		$io->writeln("└─");
		$io->newLine();
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
			$io->write("• Syncing {$config->formattedLabel} ... ");
			$performedAction = $this->managementApi->syncComponent($config->config);
			$io->writeln(sprintf("%s <fg=green>✓</>", $performedAction->value));
		}
	}
}
