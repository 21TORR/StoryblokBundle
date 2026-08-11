<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Fixtures\Components\Embed;

use Torr\Storyblok\Definition\Mapping as Storyblok;

/**
 * @final
 */
class EmbedWithNestedEmbed
{
	#[Storyblok\TextField("Headline")]
	public string $headline;

	#[Storyblok\EmbeddedField("Inner Embed", key: "inner_")]
	public EmbeddedValue $embed;
}
