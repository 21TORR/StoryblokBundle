<?php declare(strict_types=1);

namespace Torr\Storyblok\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Torr\Cli\Console\Style\TorrStyle;
use Torr\Hosting\Hosting\HostingEnvironment;
use Torr\Storyblok\Adapter\AbstractStoryblokAdapter;
use Torr\Storyblok\Adapter\StoryblokAdapterRegistry;
use Torr\Storyblok\Exception\Sync\SyncFailedException;
use Torr\Storyblok\Exception\Validation\ValidationFailedException;
use Torr\Storyblok\Manager\Sync\ComponentSync;

#[AsCommand("storyblok:definitions:sync")]
final class SyncDefinitionsCommand extends Command
{
	/**
	 * @inheritDoc
	 */
	public function __construct (
		private readonly ComponentSync $componentSync,
		private readonly StoryblokAdapterRegistry $storyblokAdapterRegistry,
		private readonly HostingEnvironment $environment,
	)
	{
		parent::__construct();
	}

	/**
	 * @inheritDoc
	 */
	protected function configure () : void
	{
		$this
			->setDescription("Syncs the local component definitions to storyblok")
			->addArgument("adapterKey", InputArgument::OPTIONAL | InputArgument::IS_ARRAY, "Storyblok adapter key. If not set, all adapters will be synced.")
			->addOption("force", null, InputOption::VALUE_NONE, "Whether to force sync");
	}

	/**
	 * @inheritDoc
	 */
	protected function execute (InputInterface $input, OutputInterface $output) : int
	{
		$io = new TorrStyle($input, $output);
		$io->title("Storyblok: Sync Definitions");

		$sync = (bool) $input->getOption("force");

		if ($sync && !$this->environment->isProduction())
		{
			$io->caution("Reject to automatically sync structure to Storyblok in non-production environment.");

			return self::SUCCESS;
		}

		/** @var string[] $adapterKeys */
		$adapterKeys = $input->getArgument("adapterKey");

		$adapters = [] !== $adapterKeys
			? array_map($this->storyblokAdapterRegistry->getByKey(...), $adapterKeys)
			: $this->storyblokAdapterRegistry->getAllAdapters();

		$result = self::SUCCESS;

		foreach ($adapters as $adapter)
		{
			$adapterSyncSuccess = $this->syncComponents($io, $sync, $adapter);

			if (!$adapterSyncSuccess)
			{
				$result = self::FAILURE;
			}
		}

		return $result;
	}

	/**
	 *
	 */
	private function syncComponents (
		TorrStyle $io,
		bool $sync,
		AbstractStoryblokAdapter $adapter,
	) : bool
	{
		$spaceInfo = $adapter->contentApi->getSpaceInfo();

		$io->comment(\sprintf(
			"Syncing components for space <fg=magenta>%s</> (<fg=yellow>%d</>)\n<fg=gray>%s</>",
			$spaceInfo->getName(),
			$spaceInfo->getId(),
			$spaceInfo->getBackendDashboardUrl(),
		));

		try
		{
			$this->componentSync->syncDefinitionsInteractively($io, $adapter, $sync);

			$io->newLine(2);
			$io->success("All done");

			return true;
		}
		catch (ValidationFailedException $exception)
		{
			$io->comment(\sprintf("<fg=red>ERROR</>\n%s", $exception->getMessage()));
			$io->error("Validation failed");

			return false;
		}
		catch (SyncFailedException $exception)
		{
			$io->comment(\sprintf("<fg=red>ERROR</>\n%s", $exception->getMessage()));
			$io->error("Sync failed");

			return false;
		}
	}
}
