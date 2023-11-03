<?php declare(strict_types=1);

namespace Torr\Storyblok\Manager\Normalizer;

use Torr\Cli\Console\Style\TorrStyle;
use Torr\Storyblok\Api\Data\ComponentImport;
use Torr\Storyblok\Manager\ComponentManager;

final class ComponentNormalizer
{
	public function __construct (
		private readonly ComponentManager $componentManager,
	) {}


	/**
	 * Validates and normalizes the components and returns them as ComponentImport.
	 *
	 * @return ComponentImport[]
	 */
	public function normalize (TorrStyle $io) : array
	{
		$normalized = [];
		$definitions = $this->componentManager->getDefinitions();

		// normalize everything to check if normalization fails
		foreach ($definitions->getComponents() as $component)
		{
			$definition = $component->definition;

			$key = \sprintf(
				"<fg=blue>%s</> (<fg=yellow>%s</>) ... ",
				$definition->name,
				$definition->key,
			);

			$io->write("Normalizing {$key} ");

			$normalized[$key] = new ComponentImport(
				$component->generateManagementApiData(),
				$definition->group,
			);

			$io->writeln("done <fg=green>✓</>");
		}

		return $normalized;
	}
}
