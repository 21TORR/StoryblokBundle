<?php declare(strict_types=1);

namespace Torr\Storyblok\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Torr\Cli\Console\Style\TorrStyle;
use Torr\Hosting\Hosting\HostingEnvironment;
use Torr\Storyblok\Adapter\AbstractStoryblokAdapter;
use Torr\Storyblok\Adapter\AdapterManager;
use Torr\Storyblok\Api\ContentApi;
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
		private readonly HostingEnvironment $environment,
		private readonly AdapterManager $adapterManager,
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

		foreach ($this->adapterManager->getAllAdapters() as $adapter)
		{
			$io->section($adapter->getDisplayName());
			$this->syncAdapter($io, $adapter, $sync);
		}
	}

	private function syncAdapter (
		TorrStyle $io,
		AbstractStoryblokAdapter $adapter,
		bool $sync,
	)
	{
		$contentApi = $adapter->contentApi;
		$spaceInfo = $contentApi->getSpaceInfo();

		$io->comment(\sprintf(
			"Syncing components for space <fg=magenta>%s</> (<fg=yellow>%d</>)\n<fg=gray>%s</>",
			$spaceInfo->getName(),
			$spaceInfo->getId(),
			$spaceInfo->getBackendDashboardUrl(),
		));

		if ($sync && !$this->environment->isProduction())
		{
			$io->caution("Reject to automatically sync structure to Storyblok in non-production environment.");

			return self::SUCCESS;
		}

		try
		{
			$this->componentSync->syncDefinitionsInteractively($adapter->managementApi, $io, $sync);

			$io->newLine(2);
			$io->success("All done");

			return self::SUCCESS;
		}
		catch (ValidationFailedException $exception)
		{
			$io->comment(\sprintf("<fg=red>ERROR</>\n%s", $exception->getMessage()));
			$io->error("Validation failed");

			return self::FAILURE;
		}
		catch (SyncFailedException $exception)
		{
			$io->comment(\sprintf("<fg=red>ERROR</>\n%s", $exception->getMessage()));
			$io->error("Sync failed");

			return self::FAILURE;
		}
	}
}
