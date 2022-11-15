<?php declare(strict_types=1);

namespace Torr\Storyblok\Data;

final class Option
{
	public function __construct (
		public readonly string $label,
		public readonly string|int $value,
	) {}
}
