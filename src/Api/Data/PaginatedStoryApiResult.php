<?php declare(strict_types=1);

namespace Torr\Storyblok\Api\Data;

use Torr\Storyblok\Story\Story;

final class PaginatedStoryApiResult
{
	/**
	 * @param array<Story> $stories
	 */
	public function __construct (
		public readonly int $perPage,
		public readonly int $totalPages,
		public readonly array $stories,
	) {}
}
