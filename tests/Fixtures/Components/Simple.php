<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Fixtures\Components;

use Torr\Storyblok\Definition\Mapping\Component;
use Torr\Storyblok\Definition\Mapping\TextField;
use Torr\Storyblok\Story\Data\Story;

/**
 * @final
 */
#[Component("simple", "Simple Label")]
class Simple extends Story
{
	#[TextField("Text")]
	public ?string $text;
}
