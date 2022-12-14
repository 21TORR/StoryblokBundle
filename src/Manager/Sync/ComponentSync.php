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
			$this->syncComponents($io);
		}
		catch (InvalidComponentConfigurationException|ApiRequestException $exception)
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
			$resolved[$key] = match (true)
			{
				"component_group_whitelist" === $key => $this->resolveComponentGroupWhitelist($value),
				$value instanceof ComponentsWithTags => $this->componentManager->getComponentKeysForTags($value->tags),
				\is_array($value) => $this->resolveComponentConfig($value),
				default => $value,
			};
		}

		return $resolved;
	}

	/**
	 * Resolves the Group's Name to their corresponding Group Uuid.
	 *
	 * @param array<string|\BackedEnum> $groupNames
	 * @return array<string>
	 */
	private function resolveComponentGroupWhitelist (array $groupNames) : array
	{
		$resolved = [];

		foreach ($groupNames as $groupName)
		{
			$uuid = $this->managementApi->getOrCreatedComponentGroupUuid($groupName);

			$resolved[$uuid] = true;
		}

		return \array_keys($resolved);
	}
}
