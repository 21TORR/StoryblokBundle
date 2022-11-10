<?php declare(strict_types=1);

namespace Torr\Storyblok\Data;

use Torr\Storyblok\Api\Manager\ComponentManager;

interface StoryblokOptionsConfiguration
{
	public function getSchemaDefinition (ComponentManager $componentManager) : array;
}
