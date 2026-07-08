<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Fixtures\Components;

use Tests\Torr\Storyblok\Fixtures\Components\Embed\EmbeddedValue;
use Torr\Storyblok\Definition\Mapping as Storyblok;
use Torr\Storyblok\Story\Data\StandaloneStory;

/**
 * @final
 */
#[Storyblok\StandaloneBlock(
	key: "with-embed",
	label: "With Embed",
)]
#[Storyblok\BlokAdminUi(
	previewField: "headline",
	icon: "...",
	iconColor: "red",
)]
class WithEmbed extends StandaloneStory
{
	#[Storyblok\TextField("Headline")]
	#[Storyblok\Required(regexp: "\d+")]
	#[Storyblok\Translatable]
	public string $headline;

	#[Storyblok\EmbeddedField("test", key: "nested_")]
	public EmbeddedValue $embed;
}
