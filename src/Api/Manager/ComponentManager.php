<?php declare(strict_types=1);

namespace Torr\Storyblok\Api\Manager;

use Torr\Storyblok\ComponentDefinition\ComponentGroups;

final class ComponentManager
{
	/**
	 * @param ComponentGroups[]|null $groupNames
	 *
	 * @return string[]
	 */
	public function getOrCreateComponentGroupUuids (?array $groupNames) : array
	{
		return [];
	}
}
