<?php declare(strict_types=1);

namespace Torr\Storyblok\Api\Data;

/**
 * @template T
 */
final class PaginatedApiResult
{
	/**
	 * @param array<array-key, T> $entries
	 */
	public function __construct (
		public readonly int $perPage,
		public readonly int $totalPages,
		/** @var array<array-key, T> */
		public readonly array $entries,
	) {}
}
