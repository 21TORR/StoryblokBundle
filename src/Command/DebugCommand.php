<?php declare(strict_types=1);

namespace Torr\Storyblok\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Torr\Cli\Console\Style\TorrStyle;
use Torr\Storyblok\Adapter\AbstractStoryblokAdapter;
use Torr\Storyblok\Adapter\StoryblokAdapterRegistry;
use Torr\Storyblok\Assets\Proxy\AssetProxy;
use Torr\Storyblok\Component\AbstractComponent;
use Torr\Storyblok\Exception\StoryblokException;
use Torr\Storyblok\Manager\ComponentManager;

use function Symfony\Component\String\u;

#[AsCommand(
	"storyblok:debug",
	description: "Displays debug info for the current Storyblok connection and config.",
)]
final class DebugCommand extends Command
{
	/**
	 *
	 */
	public function __construct (
		private readonly StoryblokAdapterRegistry $storyblokAdapterRegistry,
		private readonly ComponentManager $componentManager,
		private readonly AssetProxy $assetProxy,
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
			->addArgument("adapterKeys", InputArgument::OPTIONAL | InputArgument::IS_ARRAY, "Storyblok adapter key. If not set, all adapters will be synced.");
	}

	/**
	 *
	 */
	#[\Override]
	protected function execute (InputInterface $input, OutputInterface $output) : int
	{
		$io = new TorrStyle($input, $output);
		$io->title("Storyblok: Debug");

		/** @var string[] $adapterKeys */
		$adapterKeys = $input->getArgument("adapterKeys");

		$adapters = [] !== $adapterKeys
			? array_map($this->storyblokAdapterRegistry->getByKey(...), $adapterKeys)
			: $this->storyblokAdapterRegistry->getAllAdapters();

		$result = self::SUCCESS;

		foreach ($adapters as $adapter)
		{
			$debugInfoSuccess = $this->debugInfo($io, $adapter);

			if (!$debugInfoSuccess)
			{
				$result = self::FAILURE;
			}

			$io->newLine();
		}

		return $result;
	}

	private function debugInfo (TorrStyle $io, AbstractStoryblokAdapter $adapter) : bool
	{
		try
		{
			$this->showInfo($io, $adapter);
			$io->newLine();

			$this->showComponentsOverview($io, $adapter);
			$this->showAssetProxyStats($io);

			return true;
		}
		catch (StoryblokException $exception)
		{
			$io->error(\sprintf(
				"Failed to show debug info: %s",
				$exception->getMessage(),
			));

			return false;
		}
	}

	/**
	 * @throws StoryblokException
	 */
	private function showInfo (TorrStyle $io, AbstractStoryblokAdapter $adapter) : void
	{
		$spaceInfo = $adapter->contentApi->getSpaceInfo();
		$color = static fn (string $color, string|int $text) => \sprintf("<fg=%s>%s</>", $color, $text);

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
		AbstractStoryblokAdapter $adapter,
	) : void
	{
		[$registered, $unregistered] = $this->fetchOverview($adapter, $io->isVerbose());

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
	private function fetchOverview (AbstractStoryblokAdapter $adapter, bool $verbose) : array
	{
		$registered = [];
		$unregistered = [];
		$componentDetails = [];

		foreach ($this->componentManager->getAllUsedComponentsInAdapter($adapter) as $component)
		{
			$componentDetails[$component::getKey()] = $this->getComponentDetails($component, $verbose);
		}

		foreach ($adapter->managementApi->fetchAllRegisteredComponents() as $componentKey)
		{
			if (!isset($componentDetails[$componentKey]))
			{
				$unregistered[] = \sprintf("<fg=red>%s</>", $componentKey);
				continue;
			}

			$registered[] = [
				\sprintf("<fg=yellow>%s</>", $componentKey),
				...$componentDetails[$componentKey],
			];
		}

		return [$registered, $unregistered];
	}

	/**
	 *
	 */
	private function getComponentDetails (AbstractComponent $component, bool $verbose) : array
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

			return \sprintf("<fg=blue>%s</>", $className);
		};

		return [
			$component->getDisplayName(),
			$renderClass(get_debug_type($component)),
			$renderClass($component->getStoryClass()),
		];
	}

	private function showAssetProxyStats (TorrStyle $io) : void
	{
		$io->section("Asset Proxy");

		[$filesCount, $totalStorage] = $this->findProxiedAssetStats();

		$io->writeln(\sprintf(
			"<fg=yellow>Total number of proxied assets:</> %d",
			$filesCount,
		));
		$io->writeln(\sprintf(
			"<fg=yellow>Total storage of proxied assets:</> %s MB",
			number_format($totalStorage / 1e6, 2),
		));
	}

	/**
	 * @return array{int, int} The total files count and the total storage
	 */
	private function findProxiedAssetStats () : array
	{
		$files = Finder::create()
			->in($this->assetProxy->getStoragePath())
			->ignoreUnreadableDirs()
			->files();

		$filesCount = 0;
		$totalStorage = 0;

		foreach ($files as $file)
		{
			$totalStorage += $file->getSize();
			++$filesCount;
		}

		return [
			$filesCount,
			$totalStorage,
		];
	}
}
