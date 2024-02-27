<?php declare(strict_types=1);

namespace Torr\Storyblok\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Torr\Cli\Console\Style\TorrStyle;

#[AsCommand("storyblok:components:overview")]
final class ComponentsOverviewCommand extends Command
{
	/**
	 * @inheritDoc
	 */
	protected function execute (InputInterface $input, OutputInterface $output) : int
	{
		$io = new TorrStyle($input, $output);

		$io->title("Storyblok: Components Overview");

		$io->error("Not implemented yet");
		return self::FAILURE;
	}
}
