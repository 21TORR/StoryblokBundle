<?php declare(strict_types=1);

namespace Torr\Storyblok\Manager\Normalizer;

use Torr\Storyblok\Adapter\AbstractStoryblokAdapter;
use Torr\Storyblok\Api\Data\ComponentImport;
use Torr\Storyblok\Component\AbstractComponent;
use Torr\Storyblok\Manager\Sync\ComponentConfigResolver;

final class ComponentNormalizer
{
	public function __construct (
		private readonly ComponentConfigResolver $componentConfigResolver,
	) {}

	/**
	 * Validates and normalizes the components and returns them as ComponentImport.
	 *
	 * @param AbstractComponent[] $components
	 *
	 * @return ComponentImport[]
	 */
	public function normalize (array $components, AbstractStoryblokAdapter $adapter) : array
	{
		$normalized = [];

		// normalize everything to check if normalization fails
		foreach ($components as $component)
		{
			$formattedLabel = \sprintf(
				"<fg=blue>%s</> (<fg=yellow>%s</>)",
				$component->getDisplayName(),
				$component::getKey(),
			);

			$config = $this->componentConfigResolver->resolveComponentConfig($component->toManagementApiData());
			$config["component_group_uuid"] = $adapter->managementApi->getOrCreatedComponentGroupUuid($component->getComponentGroup());

			$normalized[] = new ComponentImport(
				$formattedLabel,
				$config,
			);
		}

		return $normalized;
	}
}
