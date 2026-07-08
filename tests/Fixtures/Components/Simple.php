<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Fixtures\Components;

use Torr\Storyblok\Definition\Mapping\StandaloneBlock;
use Torr\Storyblok\Definition\Mapping\TextField;
use Torr\Storyblok\Story\Data\StandaloneStory;

/**
 * @final
 */
#[StandaloneBlock("simple", "Simple Label")]
class Simple extends StandaloneStory
{
	#[TextField("Text")]
	public ?string $text;
}
