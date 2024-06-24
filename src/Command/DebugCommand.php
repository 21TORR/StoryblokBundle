<?php declare(strict_types=1);

namespace Torr\Storyblok\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Torr\Cli\Console\Style\TorrStyle;
use Torr\Storyblok\Api\ContentApi;
use Torr\Storyblok\Exception\StoryblokException;

#[AsCommand("storyblok:debug")]
final class DebugCommand extends Command
{
	/**
	 *
	 */
	public function __construct (
		private readonly ContentApi $contentApi,
	)
	{
		parent::__construct();
	}

	/**
	 *
	 */
	#[\Override]
	protected function execute (InputInterface $input, OutputInterface $output) : int
	{
		$io = new TorrStyle($input, $output);
		$io->title("Storyblok: Debug");
		$color = static fn (string $color, string|int $text) => sprintf("<fg=%s>%s</>", $color, $text);

		try
		{
			$spaceInfo = $this->contentApi->getSpaceInfo();

			$io->definitionList(
				["Space ID" => $color("magenta", $spaceInfo->getId())],
				["Name" => $color("blue", $spaceInfo->getName())],
				["Preview URL" => $spaceInfo->getDomain()],
				["Backend URL" => $spaceInfo->getBackendDashboardUrl()],
				["Cache Version" => $color("yellow", $spaceInfo->getCacheVersion())],
			);

			return self::SUCCESS;
		}
		catch (StoryblokException $exception)
		{
			$io->error(sprintf(
				"Failed to show debug info: %s",
				$exception->getMessage(),
			));

			return self::FAILURE;
		}
	}
}
