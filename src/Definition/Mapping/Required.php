<?php declare(strict_types=1);

namespace Torr\Storyblok\Definition\Mapping;

/**
 * @final
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
readonly class Required
{
	/**
	 */
	public function __construct (
		public bool $allowMissingData = false,
	) {}
}
