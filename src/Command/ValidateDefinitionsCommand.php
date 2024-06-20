<?php declare(strict_types=1);

namespace Torr\Storyblok\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Torr\Cli\Console\Style\TorrStyle;
use Torr\Storyblok\Api\ContentApi;
use Torr\Storyblok\Exception\Validation\ValidationFailedException;
use Torr\Storyblok\Manager\Validator\ComponentValidator;

#[AsCommand(name: "storyblok:definitions:validate", description: "Validates the local component definitions against Storyblok")]
final class ValidateDefinitionsCommand extends Command
{
	/**
	 * @inheritDoc
	 */
	public function __construct (
		private readonly ComponentValidator $componentValidator,
		private readonly ContentApi $contentApi,
	)
	{
		parent::__construct();
	}

	/**
	 * @inheritDoc
	 */
	protected function execute (InputInterface $input, OutputInterface $output) : int
	{
		$io = new TorrStyle($input, $output);
		$io->title("Storyblok: Sync Definitions");

		$spaceInfo = $this->contentApi->getSpaceInfo();

		$io->comment(sprintf(
			"Validating components for space <fg=magenta>%s</> (<fg=yellow>%d</>)\n<fg=gray>%s</>",
			$spaceInfo->getName(),
			$spaceInfo->getId(),
			$spaceInfo->getBackendDashboardUrl(),
		));

		try
		{
			$this->componentValidator->validateDefinitions();

			$io->newLine(2);
			$io->success("All definitions validated.");

			return self::SUCCESS;
		}
		catch (ValidationFailedException $exception)
		{
			$io->comment(sprintf("<fg=red>ERROR</>\n%s", $exception->getMessage()));
			$io->error("Definitions validation failed");

			return self::FAILURE;
		}
	}
}
