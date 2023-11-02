<?php declare(strict_types=1);

namespace Torr\Storyblok\Api\Data;

use Torr\Storyblok\Datasource\DatasourceEntry;

final class PaginatedDatasourceApiResult
{
	/**
	 * @param DatasourceEntry[] $entries
	 */
	public function __construct (
		public readonly int $perPage,
		public readonly int $totalPages,
		public readonly array $entries,
	) {}
}
