<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Fixtures\Components;

use Tests\Torr\Storyblok\Fixtures\Group\ComponentGroupFixture;
use Tests\Torr\Storyblok\Fixtures\Group\ComponentTagFixture;
use Torr\Storyblok\Definition\Mapping\Component;
use Torr\Storyblok\Story\Data\Story;

/**
 * @final
 */
#[Component(
	key: "full-standalone-block",
	label: "Full Standalone Block",
	description: "This is my description",
	tags: [ComponentTagFixture::Tag1, "tag2"],
	folder: ComponentGroupFixture::Folder,
)]
class FullBlock extends Story
{
}
