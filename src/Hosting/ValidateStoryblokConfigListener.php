<?php declare(strict_types=1);

namespace Torr\Storyblok\Hosting;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Torr\Hosting\Event\ValidateAppEvent;
use Torr\Storyblok\Adapter\StoryblokAdapterRegistry;
use Torr\Storyblok\Exception\Config\InvalidConfigException;

/**
 * @final
 */
readonly class ValidateStoryblokConfigListener
{
	/**
	 *
	 */
	public function __construct (
		private StoryblokAdapterRegistry $storyblokAdapterRegistry,
	) {}

	/**
	 *
	 */
	#[AsEventListener]
	public function onValidateApp (ValidateAppEvent $event) : void
	{
		$io = $event->io;
		$io->write("• Checking Storyblok Adapter configurations ... ");

		$adapters = $this->storyblokAdapterRegistry->getAllAdapters();

		$io->comment(\sprintf(
			"Found <fg=blue>%d %s</>:",
			\count($adapters),
			1 !== \count($adapters) ? "adapters" : "adapter",
		));

		foreach ($adapters as $adapter)
		{
			$io->write(\sprintf(
				"• %s ... ",
				$adapter->getDisplayName(),
			));

			try
			{
				$adapter->contentApi->getSpaceInfo();

				$io->writeln("<fg=green>valid</>");
			}
			catch (InvalidConfigException)
			{
				$io->writeln("<fg=red>invalid</>");
				$event->markAppAsInvalid("Storyblok Adapter Config");
			}
		}
	}
}
