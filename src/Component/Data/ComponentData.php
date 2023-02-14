<?php declare(strict_types=1);

namespace Torr\Storyblok\Component\Data;

/**
 * Wrapper around transformed component data.
 */
final class ComponentData
{
	/**
	 */
	public function __construct (
		public readonly string $type,
		public readonly array $data,
		public readonly mixed $previewData = null,
	) {}
}
