<?php declare(strict_types=1);

namespace Torr\Storyblok\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Torr\Cli\Console\Style\TorrStyle;
use Torr\Hosting\Hosting\HostingEnvironment;
use Torr\Storyblok\Api\Adapter\AbstractStoryblokAdapter;
use Torr\Storyblok\Exception\Sync\SyncFailedException;
use Torr\Storyblok\Exception\Validation\ValidationFailedException;
use Torr\Storyblok\Manager\StoryblokAdapterManager;
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
		private readonly StoryblokAdapterManager $adapterManager,
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

		$adapters = $this->adapterManager->getAllAdapters();

		if (0 === \count($adapters))
		{
			$io->error("No adapters found");

			return self::FAILURE;
		}

		$spaceIds = array_map(static fn (AbstractStoryblokAdapter $adapter) => (string) $adapter->contentApi->getSpaceInfo()->getId(), $adapters);

		$io->info(\sprintf(
			"Found %d adapters with space ids: %s",
			\count($adapters),
			implode(", ", $spaceIds),
		));

		foreach ($adapters as $adapter)
		{
			$returnCode = $this->syncSpace($adapter, $io, $input);

			if (self::SUCCESS !== $returnCode)
			{
				return $returnCode;
			}
		}

		return self::SUCCESS;
	}

	protected function syncSpace (AbstractStoryblokAdapter $adapter, TorrStyle $io, InputInterface $input) : int
	{
		$spaceInfo = $adapter->contentApi->getSpaceInfo();

		$io->comment(\sprintf(
			"Syncing components for space <fg=magenta>%s</> (<fg=yellow>%d</>)\n<fg=gray>%s</>",
			$spaceInfo->getName(),
			$spaceInfo->getId(),
			$spaceInfo->getBackendDashboardUrl(),
		));

		$sync = (bool) $input->getOption("force");

		if ($sync && !$this->environment->isProduction())
		{
			$io->caution("Reject to automatically sync structure to Storyblok in non-production environment.");

			return self::SUCCESS;
		}

		try
		{
			$this->componentSync->syncDefinitionsInteractively($io, $adapter, $sync);

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
