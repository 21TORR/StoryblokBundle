<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Fixtures\Components\FieldOrder;

use Torr\Storyblok\Definition\Mapping as Storyblok;

/**
 * @final
 */
readonly class FieldOrderOuterGroupEmbed
{
	#[Storyblok\TextField(label: "Label")]
	public string $first;

	#[Storyblok\EmbeddedField(label: "Inner")]
	public FieldOrderInnerEmbed $inner;

	#[Storyblok\TextField(label: "Label")]
	public string $second;
}
