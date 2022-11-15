<?php declare(strict_types=1);

namespace Torr\Storyblok\Structure\OptionsConfiguration;

use Torr\Storyblok\Data\DatasourceType;

final class StoriesOptionsConfiguration implements OptionsConfiguration
{
	public function __construct (
		public readonly string $storiesFolderPath,
		public readonly ?bool $useUuid = null,
	) {}

	public function getSerializedConfig () : array
	{
		return [
			"source" => DatasourceType::Stories->value,
			"use_uuid" => $this->useUuid,
			"folder_slug" => $this->storiesFolderPath,
		];
	}
}
