<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Fixtures\Components;

use Tests\Torr\Storyblok\Fixtures\Components\Embed\EmbedWithNestedEmbed;
use Torr\Storyblok\Definition\Mapping as Storyblok;
use Torr\Storyblok\Story\Data\Story;

/**
 * @final
 */
#[Storyblok\Component("with-nested-embed", "With NestedEmbed")]
class WithNestedEmbed extends Story
{
	#[Storyblok\TextField("Headline")]
	public string $headline;

	#[Storyblok\EmbeddedField("test", key: "nested_")]
	public EmbedWithNestedEmbed $embed;
}
