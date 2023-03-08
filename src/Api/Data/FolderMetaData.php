<?php declare(strict_types=1);

namespace Torr\Storyblok\Api\Data;

final class FolderMetaData
{
	public function __construct (
		public readonly string $url,
		public readonly string $name,
		public readonly int $sortOrder,
	) {}
}
