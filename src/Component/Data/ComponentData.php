<?php declare(strict_types=1);

namespace Torr\Storyblok\Component\Data;

/**
 * Wrapper around transformed component data.
 *
 * @todo add fields relevant to the preview editor here
 */
final class ComponentData
{
	/**
	 */
	public function __construct (
		public readonly string $type,
		public readonly array $data,
	) {}
}
