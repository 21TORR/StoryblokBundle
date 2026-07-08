<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Fixtures\Field;

use Torr\Storyblok\Definition\Mapping\NestedBlock;
use Torr\Storyblok\Definition\Mapping\Required;
use Torr\Storyblok\Definition\Mapping\TextField;
use Torr\Storyblok\Definition\Mapping\Translatable;
use Torr\Storyblok\Story\Data\NestedStory;

/**
 * @final
 */
#[NestedBlock("test", "test")]
class ComponentWithTextField extends NestedStory
{
	#[TextField("Field")]
	#[Required]
	#[Translatable]
	public string $text;
}
