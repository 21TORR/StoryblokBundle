<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Fixtures\Components;

use Torr\Storyblok\Definition\Mapping\Component;
use Torr\Storyblok\Story\Data\Story;

/**
 * @final
 */
#[Component(
	key: "full-standalone-block",
	label: "Full Standalone Block",
)]
class FullBlock extends Story
{
}
