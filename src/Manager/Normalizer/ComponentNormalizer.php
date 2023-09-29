<?php declare(strict_types=1);

namespace Torr\Storyblok\Manager\Normalizer;

use Torr\Cli\Console\Style\TorrStyle;
use Torr\Storyblok\Api\Data\ComponentImport;
use Torr\Storyblok\Manager\ComponentManager;
use Torr\Storyblok\Manager\Sync\ComponentConfigResolver;

final class ComponentNormalizer
{
	public function __construct (
		private readonly ComponentManager $componentManager,
		private readonly ComponentConfigResolver $componentConfigResolver,
	) {}


	/**
	 * Validates and normalizes the components and returns them as ComponentImport.
	 *
	 * @return ComponentImport[]
	 */
	public function normalize (TorrStyle $io) : array
	{
		$normalized = [];

		// normalize everything to check if normalization fails
		foreach ($this->componentManager->getAllComponents() as $component)
		{
			$key = \sprintf(
				"<fg=blue>%s</> (<fg=yellow>%s</>) ... ",
				$component->getDisplayName(),
				$component::getKey(),
			);

			$io->write("Normalizing {$key} ");

			$normalized[$key] = new ComponentImport(
				$this->componentConfigResolver->resolveComponentConfig($component->toManagementApiData()),
				$component->getComponentGroup(),
			);

			$io->writeln("done <fg=green>✓</>");
		}

		return $normalized;
	}
}
