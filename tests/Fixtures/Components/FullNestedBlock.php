<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Fixtures\Components;

use Torr\Storyblok\Definition\Mapping\Component;
use Torr\Storyblok\Story\Data\Block;

/**
 * @final
 */
#[Component(
	key: "full-nested-block",
	label: "Full Nested Block",
	folder: "folder2",
)]
class FullNestedBlock extends Block
{
}
