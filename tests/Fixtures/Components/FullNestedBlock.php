<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Fixtures\Components;

use Torr\Storyblok\Definition\Mapping\NestedBlock;
use Torr\Storyblok\Story\Data\NestedStory;

/**
 * @final
 */
#[NestedBlock(
	key: "full-nested-block",
	label: "Full Nested Block",
)]
class FullNestedBlock extends NestedStory
{
}
