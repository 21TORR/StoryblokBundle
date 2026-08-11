<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Fixtures\Components\FieldOrder;

use Torr\Storyblok\Definition\Mapping as Storyblok;

/**
 * @final
 */
readonly class FieldOrderInnerEmbed
{
	#[Storyblok\TextField(label: "Label")]
	public string $a;

	#[Storyblok\TextField(label: "Label")]
	public string $b;
}
