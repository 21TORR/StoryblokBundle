<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Fixtures\Components;

use Torr\Storyblok\Definition\Mapping\Component;
use Torr\Storyblok\Definition\Mapping\Required;
use Torr\Storyblok\Definition\Mapping\TextField;
use Torr\Storyblok\Definition\Mapping\Translatable;
use Torr\Storyblok\Story\Data\Story;

/**
 * @final
 */
#[Component("simple-translatable", "Simple Translatable Label")]
class SimpleTranslatable extends Story
{
	#[TextField("Text")]
	#[Translatable]
	#[Required]
	public ?string $text;
}
