<?php declare(strict_types=1);

namespace Torr\Storyblok\Structure\OptionsConfiguration;

use Torr\Storyblok\Data\DatasourceType;

final class ExternalOptionsConfiguration implements OptionsConfiguration
{
	public function __construct (
		public readonly string $datasourceUrl,
	) {}


	public function getSerializedConfig () : array
	{
		return [
			"source" => DatasourceType::ExternalApi->value,
			"external_datasource" => $this->datasourceUrl,
		];
	}
}
