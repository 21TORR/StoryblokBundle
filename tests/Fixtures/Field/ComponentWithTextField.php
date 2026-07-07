<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Fixtures\Field;

use Torr\Storyblok\Definition\Mapping\Blok;
use Torr\Storyblok\Definition\Mapping\Required;
use Torr\Storyblok\Definition\Mapping\TextField;
use Torr\Storyblok\Definition\Mapping\Translatable;
use Torr\Storyblok\Story\Data\BlokStory;

/**
 * @final
 */
#[Blok("test", "test")]
class ComponentWithTextField extends BlokStory
{
	#[TextField("Field")]
	#[Required]
	#[Translatable]
	public string $text;
}
