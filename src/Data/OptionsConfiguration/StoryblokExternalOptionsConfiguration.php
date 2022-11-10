<?php declare(strict_types=1);

namespace Torr\Storyblok\Data\OptionsConfiguration;

use Torr\Storyblok\Api\Manager\ComponentManager;
use Torr\Storyblok\Data\StoryblokDatasourceType;
use Torr\Storyblok\Data\StoryblokOptionsConfiguration;

final class StoryblokExternalOptionsConfiguration implements StoryblokOptionsConfiguration
{
	public function __construct (
		private readonly string $datasourceUrl,
	) {}

	public function getDatasourceUrl () : string
	{
		return $this->datasourceUrl;
	}

	public function getSchemaDefinition (ComponentManager $componentManager) : array
	{
		return [
			"source" => StoryblokDatasourceType::ExternalApi->value,
			"external_datasource" => $this->getDatasourceUrl(),
		];
	}
}
