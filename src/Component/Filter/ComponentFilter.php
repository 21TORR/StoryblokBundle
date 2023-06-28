<?php declare(strict_types=1);

namespace Torr\Storyblok\Component\Filter;

final class ComponentFilter
{
	/**
	 */
	public function __construct (
		/** @var array<string|\BackedEnum> */
		public readonly array $tags = [],
		/** @var array<string|\BackedEnum> */
		public readonly array $components = [],
	) {}


	/**
	 */
	public static function tags (string|\BackedEnum ...$tags) : self
	{
		return new self(tags: $tags);
	}
}
