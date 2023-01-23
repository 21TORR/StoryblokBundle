<?php declare(strict_types=1);

namespace Torr\Storyblok\Field\Data\Helper;

/**
 * Data that should be inlined when returned from a transformation data processor
 */
final class InlinedTransformedData
{
	/**
	 */
	public function __construct (
		public readonly array $data,
	) {}
}
