<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Fixtures\Components\Tags;

use Torr\Storyblok\Definition\Mapping\Component;
use Torr\Storyblok\Definition\Mapping\TextField;
use Torr\Storyblok\Story\Data\Story;

/**
 * @final
 */
#[Component(
	key: "with-tag-ab",
	label: "With Tag AB",
	tags: [
		"b",
		FixtureComponentTags::A,
	],
)]
class ComponentWithTagAB extends Story
{
	#[TextField("Headline")]
	public ?string $headline;
}
