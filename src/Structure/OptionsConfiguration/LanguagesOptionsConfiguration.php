<?php declare(strict_types=1);

namespace Torr\Storyblok\Structure\OptionsConfiguration;

use Torr\Storyblok\Data\DatasourceType;

final class LanguagesOptionsConfiguration implements OptionsConfiguration
{
	public function getSerializedConfig () : array
	{
		return [
			"source" => DatasourceType::Languages->value,
		];
	}
}
