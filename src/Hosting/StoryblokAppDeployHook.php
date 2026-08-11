<?php declare(strict_types=1);

namespace Torr\Storyblok\Hosting;

use Torr\Hosting\Deployment\DeployAppHookInterface;
use Torr\Hosting\Deployment\TaskCli;
use Torr\Hosting\Hosting\HostingEnvironment;
use Torr\Storyblok\Adapter\StoryblokAdapterRegistry;
use Torr\Storyblok\Command\SyncDefinitionsCommand;
use Torr\Storyblok\Config\StoryblokBundleConfig;

/**
 * @final
 */
readonly class StoryblokAppDeployHook implements DeployAppHookInterface
{
	/**
	 */
	public function __construct (
		private HostingEnvironment $environment,
		private StoryblokBundleConfig $settings,
		private StoryblokAdapterRegistry $adapterRegistry,
		private SyncDefinitionsCommand $syncDefinitionsCommand,
	) {}

	/**
	 *
	 */
	#[\Override]
	public function getLabel () : string
	{
		return "Storyblok Bundle: Sync Definitions";
	}

	/**
	 *
	 */
	#[\Override]
	public function runDeployApp (TaskCli $io) : void
	{
		if (!$this->shouldSync())
		{
			$io->caution(\sprintf(
				"Sync disabled for environment '%s'",
				$this->environment->getTier()->value,
			));

			return;
		}

		foreach ($this->adapterRegistry->getAllAdapters() as $adapter)
		{
			$io->section(\sprintf(
				"Syncing adapter: %s",
				$adapter->getDisplayName(),
			));

			$this->syncDefinitionsCommand->syncComponents($io, true, $adapter);
		}

		$io->success("All adapters synced");
	}

	/**
	 *
	 */
	private function shouldSync () : bool
	{
		return
			($this->environment->isProduction() && $this->settings->syncDefinitionsOnAppDeployInProduction)
			|| ($this->environment->isStaging() && $this->settings->syncDefinitionsOnAppDeployInStaging);
	}
}
