<?php declare(strict_types=1);

namespace Torr\Storyblok\Manager\Normalizer;

use Torr\Storyblok\Api\Data\ComponentImport;
use Torr\Storyblok\Api\ManagementApi;
use Torr\Storyblok\Manager\ComponentManager;
use Torr\Storyblok\Manager\Sync\ComponentConfigResolver;

final class ComponentNormalizer
{
	public function __construct (
		private readonly ComponentManager $componentManager,
		private readonly ComponentConfigResolver $componentConfigResolver,
		private readonly ManagementApi $managementApi,
	) {}

	/**
	 * Validates and normalizes the components and returns them as ComponentImport.
	 *
	 * @return ComponentImport[]
	 */
	public function normalize () : array
	{
		$normalized = [];

		// normalize everything to check if normalization fails
		foreach ($this->componentManager->getAllComponents() as $component)
		{
			$formattedLabel = sprintf(
				"<fg=blue>%s</> (<fg=yellow>%s</>)",
				$component->getDisplayName(),
				$component::getKey(),
			);

			$config = $this->componentConfigResolver->resolveComponentConfig($component->toManagementApiData());
			$config["component_group_uuid"] = $this->managementApi->getOrCreatedComponentGroupUuid($component->getComponentGroup());

			$normalized[] = new ComponentImport(
				$formattedLabel,
				$config,
			);
		}

		return $normalized;
	}
}
