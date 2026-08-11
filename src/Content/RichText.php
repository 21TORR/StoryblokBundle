<?php declare(strict_types=1);

namespace Torr\Storyblok\Content;

/**
 * @final
 */
readonly class RichText
{
	public function __construct (
		public array $content,
	) {}
}
