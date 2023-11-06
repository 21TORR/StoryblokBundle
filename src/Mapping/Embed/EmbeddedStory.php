<?php declare(strict_types=1);

namespace Torr\Storyblok\Mapping\Embed;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
final readonly class EmbeddedStory
{
	public string $prefix;

	/**
	 */
	public function __construct (
		string $prefix,
		public string $label,
	)
	{
		$this->prefix = \rtrim($prefix, "_") . "_";
	}
}
