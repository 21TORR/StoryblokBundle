<?php declare(strict_types=1);

namespace Torr\Storyblok\Datasource;

final class DatasourceEntry
{
	public function __construct (
		public readonly string $label,
		public readonly string $value,
	) {}
}
