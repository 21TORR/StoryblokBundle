<?php declare(strict_types=1);

namespace Torr\Storyblok\Definition\Mapping;

use Torr\Storyblok\Definition\Data\AdminUiSettings;

/**
 * @final
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
readonly class Document
{
	/**
	 */
	public function __construct (
		public string $key,
		public string $label,
		public ?AdminUiSettings $adminUi = null,
	) {}
}
