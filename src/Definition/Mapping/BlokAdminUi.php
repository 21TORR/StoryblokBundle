<?php declare(strict_types=1);

namespace Torr\Storyblok\Definition\Mapping;

/**
 * @final
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
readonly class BlokAdminUi
{
	/**
	 */
	public function __construct (
		public ?string $previewField = null,
		public ?string $previewTemplate = null,
		public ?string $thumbnailUrl = null,
		public ?string $icon = null,
		public ?string $iconColor = null,
	) {}
}
