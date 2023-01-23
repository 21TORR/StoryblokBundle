<?php declare(strict_types=1);

namespace Torr\Storyblok\Field\Data;

/**
 * Data that should be inlined when returned from transformed data
 */
final class InlinedTransformedData
{
	/**
	 */
	public function __construct (
		public readonly array $data,
	) {}
}
