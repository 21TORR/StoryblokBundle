<?php declare(strict_types=1);

namespace Torr\Storyblok\Assets\Url\Data;

final readonly class ParsedStoryblokAssetUrl
{
	public function __construct (
		public string $spaceId,
		public string $path,
	) {}
}
