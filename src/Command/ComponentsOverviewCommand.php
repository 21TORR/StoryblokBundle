<?php declare(strict_types=1);

namespace Torr\Storyblok\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Torr\Cli\Console\Style\TorrStyle;
use Torr\Storyblok\Api\ManagementApi;
use Torr\Storyblok\Exception\Component\UnknownComponentKeyException;
use Torr\Storyblok\Manager\ComponentManager;

use function Symfony\Component\String\u;

#[AsCommand("storyblok:components:overview")]
final class ComponentsOverviewCommand extends Command
{
	/**
	 * @inheritDoc
	 */
	public function __construct (
		private readonly ManagementApi $managementApi,
		private readonly ComponentManager $componentManager,
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

		$io->title("Storyblok: Components Overview");

		[$registered, $unregistered] = $this->fetchOverview($output->isVerbose());

		if (!empty($registered))
		{
			$io->section("Registered Components");
			$io->table(
				[
					"Key",
					"Name",
					"Component",
					"Story",
				],
				$registered,
			);
		}

		if (!empty($unregistered))
		{
			$io->section("Unknown Components");
			$io->listing($unregistered);
		}

		return 0;
	}

	/**
	 *
	 */
	private function fetchOverview (bool $verbose) : array
	{
		$registered = [];
		$unregistered = [];

		foreach ($this->managementApi->fetchAllRegisteredComponents() as $componentKey)
		{
			$details = $this->getComponentDetails($componentKey, $verbose);

			if (null === $details)
			{
				$unregistered[] = sprintf("<fg=red>%s</>", $componentKey);
				continue;
			}

			$registered[] = [
				sprintf("<fg=yellow>%s</>", $componentKey),
				...$details,
			];
		}

		return [$registered, $unregistered];
	}

	/**
	 *
	 */
	private function getComponentDetails (string $componentKey, bool $verbose) : ?array
	{
		$renderClass = static function (?string $className) use ($verbose)
		{
			if (null === $className)
			{
				return "<fg=gray>—</>";
			}

			if (!$verbose)
			{
				$className = u($className)->afterLast("\\")->toString();
			}

			return sprintf("<fg=blue>%s</>", $className);
		};

		try
		{
			$component = $this->componentManager->getComponent($componentKey);

			return [
				$component->getDisplayName(),
				$renderClass(get_debug_type($component)),
				$renderClass($component->getStoryClass()),
			];
		}
		catch (UnknownComponentKeyException)
		{
			return null;
		}
	}
}
