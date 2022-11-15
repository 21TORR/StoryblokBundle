<?php declare(strict_types=1);

namespace Torr\Storyblok\Structure\OptionsConfiguration;

use Torr\Storyblok\Data\DatasourceType;

final class DatasourceOptionsConfiguration implements OptionsConfiguration
{
	public function __construct (
		public readonly ?string $datasourceSlug = null,
	) {}


	public function getSerializedConfig () : array
	{
		return [
			"source" => DatasourceType::Datasource->value,
			"datasource_slug" => $this->datasourceSlug,
		];
	}
}
