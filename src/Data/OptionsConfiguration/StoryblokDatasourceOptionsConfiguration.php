<?php declare(strict_types=1);

namespace Torr\Storyblok\Data\OptionsConfiguration;

use Torr\Storyblok\Api\Manager\ComponentManager;
use Torr\Storyblok\Data\StoryblokDatasourceType;
use Torr\Storyblok\Data\StoryblokOptionsConfiguration;

final class StoryblokDatasourceOptionsConfiguration implements StoryblokOptionsConfiguration
{
	public function __construct (
		private readonly ?string $datasourceSlug = null,
	) {}

	public function getDatasourceSlug () : ?string
	{
		return $this->datasourceSlug;
	}

	public function getSchemaDefinition (ComponentManager $componentManager) : array
	{
		return [
			"source" => StoryblokDatasourceType::Datasource->value,
			"datasource_slug" => $this->getDatasourceSlug(),
		];
	}
}
