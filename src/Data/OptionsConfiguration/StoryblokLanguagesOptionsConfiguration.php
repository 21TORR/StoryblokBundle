<?php declare(strict_types=1);

namespace Torr\Storyblok\Data\OptionsConfiguration;

use Torr\Storyblok\Api\Manager\ComponentManager;
use Torr\Storyblok\Data\StoryblokDatasourceType;
use Torr\Storyblok\Data\StoryblokOptionsConfiguration;

final class StoryblokLanguagesOptionsConfiguration implements StoryblokOptionsConfiguration
{
	public function getSchemaDefinition (ComponentManager $componentManager) : array
	{
		return [
			"source" => StoryblokDatasourceType::Languages->value,
		];
	}
}
