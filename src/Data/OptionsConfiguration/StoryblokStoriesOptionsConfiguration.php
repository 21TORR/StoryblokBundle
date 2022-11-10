<?php declare(strict_types=1);

namespace Torr\Storyblok\Data\OptionsConfiguration;

use Torr\Storyblok\Api\Manager\ComponentManager;
use Torr\Storyblok\Data\StoryblokDatasourceType;
use Torr\Storyblok\Data\StoryblokOptionsConfiguration;

final class StoryblokStoriesOptionsConfiguration implements StoryblokOptionsConfiguration
{
	public function __construct (
		private readonly string $storiesFolderPath,
		private readonly ?bool $useUuid = null,
	) {}

	public function getStoriesFolderPath () : ?string
	{
		return $this->storiesFolderPath;
	}

	public function getUseUuid () : ?bool
	{
		return $this->useUuid;
	}

	public function getSchemaDefinition (ComponentManager $componentManager) : array
	{
		return [
			"source" => StoryblokDatasourceType::Stories->value,
			"use_uuid" => $this->getUseUuid(),
			"folder_slug" => $this->getStoriesFolderPath(),
		];
	}
}
