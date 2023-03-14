<?php declare(strict_types=1);

namespace Torr\Storyblok\Field\Data;

final class AssetData
{
	/**
	 */
	public function __construct (
		public readonly string $url,
		public readonly ?int $id = null,
		public readonly ?string $alt = null,
		public readonly ?string $name = null,
		public readonly ?string $focus = null,
		public readonly ?string $title = null,
		public readonly ?string $source = null,
		public readonly ?string $copyright = null,
		public readonly bool $isExternal = false,
		public readonly ?int $width = null,
		public readonly ?int $height = null,
	) {}
}
