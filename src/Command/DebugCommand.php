<?php declare(strict_types=1);

namespace Torr\Storyblok\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Torr\Cli\Console\Style\TorrStyle;
use Torr\Storyblok\Manager\ComponentManager;

#[AsCommand("storyblok:debug")]
final class DebugCommand extends Command
{
	/**
	 * @inheritDoc
	 */
	public function __construct (
		private readonly ComponentManager $componentManager,
	)
	{
		parent::__construct();
	}


	/**
	 * @inheritDoc
	 */
	protected function execute (InputInterface $input, OutputInterface $output)
	{
		$io = new TorrStyle($input, $output);
		$io->title("Storyblok: Debug");

		$definitions = $this->componentManager->getDefinitions();
		dump($definitions);

		foreach ($definitions->getComponents() as $component)
		{
			$io->section($component->definition->name);
			dump($component->generateManagementApiData());
		}

		return self::SUCCESS;
	}
}
