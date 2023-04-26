<?php declare(strict_types=1);

namespace Torr\Storyblok\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Torr\Cli\Console\Style\TorrStyle;
use Torr\Storyblok\Config\StoryblokConfig;
use Torr\Storyblok\Exception\Sync\SyncFailedException;
use Torr\Storyblok\Manager\Sync\ComponentSync;

#[AsCommand("storyblok:definitions:sync")]
final class SyncDefinitionsCommand extends Command
{
	/**
	 * @inheritDoc
	 */
	public function __construct (
		private readonly ComponentSync $componentSync,
		private readonly StoryblokConfig $config,
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
			->addOption("sync", null, InputOption::VALUE_NONE, "Whether to actually sync");
	}

	/**
	 * @inheritDoc
	 */
	protected function execute (InputInterface $input, OutputInterface $output) : int
	{
		$io = new TorrStyle($input, $output);
		$io->title("Storyblok: Sync Definitions");

		$io->comment(\sprintf(
			"Syncing components for space <fg=magenta>%d</>\n<fg=gray>%s</>",
			$this->config->getSpaceId(),
			$this->config->getStoryblokSpaceUrl(),
		));

		$sync = $input->getOption("sync");

		try
		{
			$this->componentSync->syncDefinitions($io, $sync);

			if (!$sync)
			{
				$io->newLine(2);
				$io->writeln("<info>Dry-run mode selected, so nothing was actually synced to Storyblok.</>");
			}

			$io->newLine(2);
			$io->success("All done");
			return 0;
		}
		catch (SyncFailedException $exception)
		{
			$io->comment(\sprintf("<fg=red>ERROR</>\n%s", $exception->getMessage()));
			$io->error("Sync failed");
			return 1;
		}
	}
}
