<?php declare(strict_types=1);

namespace Torr\Storyblok\Data;

final class StoryblokOption
{
	public function __construct (
		private readonly string $label,
		private readonly string|int $value,
	) {}

	public function getLabel () : string
	{
		return $this->label;
	}

	public function getValue () : int|string
	{
		return $this->value;
	}
}
