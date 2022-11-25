<?php declare(strict_types=1);

namespace Torr\Storyblok\Field\Data;

final class ExternalLinkData
{
	public function __construct (
		public readonly string $url,
	) {}
}
