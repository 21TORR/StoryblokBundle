<?php declare(strict_types=1);

namespace Torr\Storyblok\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Torr\Cli\Console\Style\TorrStyle;
use Torr\Storyblok\Api\ContentApi;
use Torr\Storyblok\Api\ManagementApi;
use Torr\Storyblok\Exception\Component\UnknownComponentKeyException;
use Torr\Storyblok\Exception\StoryblokException;
use Torr\Storyblok\Manager\ComponentManager;

use function Symfony\Component\String\u;

#[AsCommand(
	"storyblok:debug",
	description: "Displays debug info for the current Storyblok connection and config.",
	// TODO v4: remove alias
	aliases: ["storyblok:components:overview"],
)]
final class DebugCommand extends Command
{
	/**
	 *
	 */
	public function __construct (
		private readonly ContentApi $contentApi,
		private readonly ManagementApi $managementApi,
		private readonly ComponentManager $componentManager,
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

		// TODO v4: remove check
		if ("storyblok:debug" !== $input->getFirstArgument())
		{
			$message = sprintf(
				"The command `%s` is deprecated. Use `%s` instead.",
				$input->getFirstArgument(),
				"storyblok:debug",
			);
			trigger_deprecation("21torr/storyblok", "3.13.0", $message);
			$io->caution($message);
		}

		try
		{
			$this->showInfo($io);
			$io->newLine();

			$this->showComponentsOverview($io);

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

	/**
	 * @throws StoryblokException
	 */
	private function showInfo (TorrStyle $io) : void
	{
		$spaceInfo = $this->contentApi->getSpaceInfo();
		$color = static fn (string $color, string|int $text) => sprintf("<fg=%s>%s</>", $color, $text);

		$io->definitionList(
			["Space ID" => $color("magenta", $spaceInfo->getId())],
			["Name" => $color("blue", $spaceInfo->getName())],
			["Preview URL" => $spaceInfo->getDomain()],
			["Backend URL" => $spaceInfo->getBackendDashboardUrl()],
			["Cache Version" => $color("yellow", $spaceInfo->getCacheVersion())],
		);
	}

	/**
	 *
	 */
	private function showComponentsOverview (
		TorrStyle $io,
	) : void
	{
		[$registered, $unregistered] = $this->fetchOverview($io->isVerbose());

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
