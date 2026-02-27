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
use Torr\Storyblok\Debug\DebugInfoCliRenderer;
use Torr\Storyblok\Exception\StoryblokException;
use Torr\Storyblok\Manager\ComponentManager;

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
		private readonly DebugInfoCliRenderer $infoRenderer,
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
			->addArgument("adapterKeys", InputArgument::OPTIONAL | InputArgument::IS_ARRAY, "Storyblok adapter key. If not set, info for all adapters will be shown.");
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
		$verbose = $io->isVerbose();

		$adapters = [] !== $adapterKeys
			? array_map($this->storyblokAdapterRegistry->getByKey(...), $adapterKeys)
			: $this->storyblokAdapterRegistry->getAllAdapters();

		$result = self::SUCCESS;

		foreach ($adapters as $adapter)
		{
			$debugInfoSuccess = $this->showAdapterDebugInfo($io, $adapter);

			if (!$debugInfoSuccess)
			{
				$result = self::FAILURE;
			}

			$io->newLine();
		}

		$this->showComponentLibrary($io, $verbose);
		$this->showAssetProxyStats($io);

		return $result;
	}

	private function showAdapterDebugInfo (TorrStyle $io, AbstractStoryblokAdapter $adapter) : bool
	{
		try
		{
			$io->headline(\sprintf("Storyblok Adapter: %s", $adapter->getDisplayName()));

			$this->showAdapterHeader($io, $adapter);
			$io->newLine();

			$io->section("Registered Components");
			$usedComponents = $this->componentManager->getAllUsedComponentsInAdapter($adapter);
			$rows = [];
			usort(
				$usedComponents,
				static fn (AbstractComponent $a, AbstractComponent $b) => $a::getKey() <=> $b::getKey(),
			);

			foreach ($usedComponents as $component)
			{
				$rows[] = [
					\sprintf("<fg=yellow>%s</>", $component::getKey()),
					$component->getDisplayName(),
					$this->infoRenderer->renderComponentType($component->componentType),
				];
			}

			$io->table(
				headers: [
					"Key",
					"Name",
					"Type",
				],
				rows: $rows,
			);

			$unknownComponents = array_diff(
				array_map(
					static fn (AbstractComponent $component) => $component::getKey(),
					$usedComponents,
				),
				$adapter->managementApi->fetchAllRegisteredComponents(),
			);

			if ([] !== $unknownComponents)
			{
				$io->block(
					"Found unknown components in this space:",
					"INFO",
					'fg=white;bg=blue',
					' ',
					true,
				);
				$io->listing($unknownComponents);
			}

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
	private function showAdapterHeader (TorrStyle $io, AbstractStoryblokAdapter $adapter) : void
	{
		$spaceInfo = $adapter->contentApi->getSpaceInfo();
		$color = static fn (string $color, string|int $text) => \sprintf("<fg=%s>%s</>", $color, $text);

		$io->definitionList(
			["Adapter Key" => $color("yellow", $adapter->getKey())],
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
	private function showComponentLibrary (TorrStyle $io, bool $showFQCN = false) : void
	{
		$io->headline("Component Library");
		$io->comment("This is the list of all available components in the component manager. Regardless of whether they are actually used in any adapter");

		$usedComponents = $this->getUsedComponents();
		$table = $io->createTable()
			->setHeaders([
				"Usage",
				"Key",
				"Name",
				"Type",
				"Component",
				"Story",
			]);
		$unused = [];

		foreach ($this->componentManager->getAllComponents() as $component)
		{
			$isUsed = \array_key_exists($component::getKey(), $usedComponents);

			if (!$isUsed)
			{
				$unused[] = \sprintf("<fg=red>%s</>", $component::getKey());
			}

			$table->addRow([
				\sprintf("<fg=yellow>%s</>", $component::getKey()),
				$component->getDisplayName(),
				$isUsed
					? "<fg=green>used</>"
					: "<fg=red>unused</>",
				$this->infoRenderer->renderComponentType($component->componentType),
				$this->infoRenderer->renderClassName(get_debug_type($component), $showFQCN),
				$this->infoRenderer->renderClassName($component->getStoryClass(), $showFQCN),
			]);
		}

		if ($showFQCN)
		{
			$table->setVertical();
		}

		$table->render();

		// display unused
		if ([] !== $unused)
		{
			$io->newLine();
			$io->headline("Unused Components");
			$io->caution("Found unused but defined components");
			$io->listing($unused);
		}
	}

	/**
	 *
	 */
	private function showAssetProxyStats (TorrStyle $io) : void
	{
		$io->headline("Asset Proxy");

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

	/**
	 * @return array<string, AbstractComponent>
	 */
	private function getUsedComponents () : array
	{
		$used = [];

		foreach ($this->storyblokAdapterRegistry->getAllAdapters() as $adapter)
		{
			foreach ($this->componentManager->getAllUsedComponentsInAdapter($adapter) as $component)
			{
				$used[$component::getKey()] = $component;
			}
		}

		return $used;
	}
}
