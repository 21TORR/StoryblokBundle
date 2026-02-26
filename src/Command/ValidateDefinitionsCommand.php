<?php declare(strict_types=1);

namespace Torr\Storyblok\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Torr\Cli\Console\Style\TorrStyle;
use Torr\Storyblok\Adapter\AbstractStoryblokAdapter;
use Torr\Storyblok\Adapter\StoryblokAdapterRegistry;
use Torr\Storyblok\Exception\Validation\ValidationFailedException;
use Torr\Storyblok\Manager\Validator\ComponentValidator;

#[AsCommand(name: "storyblok:definitions:validate")]
final class ValidateDefinitionsCommand extends Command
{
	/**
	 * @inheritDoc
	 */
	public function __construct (
		private readonly ComponentValidator $componentValidator,
		private readonly StoryblokAdapterRegistry $storyblokAdapterRegistry,
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
			->setDescription("Validates the local component definitions against Storyblok")
			->addArgument("adapterKeys", InputArgument::OPTIONAL | InputArgument::IS_ARRAY, "Storyblok adapter key. If not set, all adapters will be validated.");
	}

	/**
	 * @inheritDoc
	 */
	protected function execute (InputInterface $input, OutputInterface $output) : int
	{
		$io = new TorrStyle($input, $output);
		$io->title("Storyblok: Sync Definitions");

		/** @var string[] $adapterKeys */
		$adapterKeys = $input->getArgument("adapterKeys");

		$adapters = [] !== $adapterKeys
			? array_map($this->storyblokAdapterRegistry->getByKey(...), $adapterKeys)
			: $this->storyblokAdapterRegistry->getAllAdapters();

		$result = self::SUCCESS;

		foreach ($adapters as $adapter)
		{
			$validateDefinitionsSuccess = $this->validateDefinitions($io, $adapter);

			if (!$validateDefinitionsSuccess)
			{
				$result = self::FAILURE;
			}
		}

		return $result;
	}

	private function validateDefinitions (TorrStyle $io, AbstractStoryblokAdapter $adapter) : bool
	{
		$spaceInfo = $adapter->contentApi->getSpaceInfo();

		$io->comment(\sprintf(
			"Validating components for space <fg=magenta>%s</> (<fg=yellow>%d</>)\n<fg=gray>%s</>",
			$spaceInfo->getName(),
			$spaceInfo->getId(),
			$spaceInfo->getBackendDashboardUrl(),
		));

		try
		{
			$this->componentValidator->validateComponentsInAdapter($adapter);

			$io->newLine(2);
			$io->success("All definitions validated.");

			return true;
		}
		catch (ValidationFailedException $exception)
		{
			$io->comment(\sprintf("<fg=red>ERROR</>\n%s", $exception->getMessage()));
			$io->error("Definitions validation failed");

			return false;
		}
	}
}
