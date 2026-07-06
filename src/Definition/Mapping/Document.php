<?php declare(strict_types=1);

namespace Torr\Storyblok\Definition\Mapping;

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
		public string $label
	) {}
}
