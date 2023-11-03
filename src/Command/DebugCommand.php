<?php declare(strict_types=1);

namespace Torr\Storyblok\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Torr\Cli\Console\Style\TorrStyle;
use Torr\Storyblok\Api\ContentApi;
use Torr\Storyblok\Manager\ComponentManager;

#[AsCommand("storyblok:debug")]
final class DebugCommand extends Command
{
	/**
	 * @inheritDoc
	 */
	public function __construct (
		private readonly ComponentManager $componentManager,
		private readonly ContentApi $contentApi,
	)
	{
		parent::__construct();
	}

	/**
	 * @inheritDoc
	 */
	protected function configure ()
	{
		$this
			->addOption("fetch")
			->addOption("sync");
	}


	/**
	 * @inheritDoc
	 */
	protected function execute (InputInterface $input, OutputInterface $output) : int
	{
		$io = new TorrStyle($input, $output);
		$io->title("Storyblok: Debug");

		if ($input->getOption("fetch"))
		{
			$stories = $this->contentApi->fetchAllStories(null);
			dd($stories);
		}

		$definitions = $this->componentManager->getDefinitions();
		dump($definitions);

		if ($input->getOption("sync"))
		{
			foreach ($definitions->getComponents() as $component)
			{
				$io->section($component->definition->name);
				dump($component->generateManagementApiData());
			}
		}

		return self::SUCCESS;
	}
}
