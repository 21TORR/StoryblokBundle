<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Fixtures\Components;

use Tests\Torr\Storyblok\Fixtures\Components\Embed\EmbedWithNestedEmbed;
use Torr\Storyblok\Definition\Mapping as Storyblok;
use Torr\Storyblok\Story\Data\DocumentStory;

/**
 * @final
 */
#[Storyblok\Document("with-nested-embed", "With NestedEmbed")]
class DocumentWithNestedEmbed extends DocumentStory
{
	#[Storyblok\TextField("Headline")]
	public string $headline;

	#[Storyblok\EmbeddedField("test", key: "nested_")]
	public EmbedWithNestedEmbed $embed;
}
