<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Fixtures\Components;

use Tests\Torr\Storyblok\Fixtures\Components\Embed\EmbedWithNestedEmbed;
use Torr\Storyblok\Definition\Mapping as Storyblok;
use Torr\Storyblok\Story\Data\StandaloneStory;

/**
 * @final
 */
#[Storyblok\StandaloneBlock("with-nested-embed", "With NestedEmbed")]
class StandaloneWithNestedEmbed extends StandaloneStory
{
	#[Storyblok\TextField("Headline")]
	public string $headline;

	#[Storyblok\EmbeddedField("test", key: "nested_")]
	public EmbedWithNestedEmbed $embed;
}
