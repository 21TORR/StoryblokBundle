<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Fixtures\Components;

use Torr\Storyblok\Definition\Mapping as Storyblok;
use Torr\Storyblok\Story\Data\DocumentStory;

/**
 * @final
 */
#[Storyblok\Document("with-blocks", "With Blocks")]
class WithBlocks extends DocumentStory
{
	#[Storyblok\TextField("Headline")]
	public string $headline;

	#[Storyblok\BloksField("Blocks")]
	public array $blocks;
}
