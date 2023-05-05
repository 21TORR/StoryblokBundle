<?php declare(strict_types=1);

namespace Torr\Storyblok\Field\Data;

final class RichTextEmailLinkData
{
	public function __construct (
		public readonly ?string $uuid,
		public readonly string $email,
		public readonly ?string $anchor,
		public readonly ?string $target,
	) {}
}
