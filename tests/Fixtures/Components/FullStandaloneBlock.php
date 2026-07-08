<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Fixtures\Components;

use Torr\Storyblok\Definition\Mapping\StandaloneBlock;
use Torr\Storyblok\Story\Data\StandaloneStory;

/**
 * @final
 */
#[StandaloneBlock(
	key: "full-standalone-block",
	label: "Full Standalone Block",
)]
class FullStandaloneBlock extends StandaloneStory
{
}
