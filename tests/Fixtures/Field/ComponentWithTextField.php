<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Fixtures\Field;

use Torr\Storyblok\Definition\Mapping\Component;
use Torr\Storyblok\Definition\Mapping\Required;
use Torr\Storyblok\Definition\Mapping\TextField;
use Torr\Storyblok\Definition\Mapping\Translatable;
use Torr\Storyblok\Story\Data\Block;

/**
 * @final
 */
#[Component("test", "test")]
class ComponentWithTextField extends Block
{
	#[TextField("Field")]
	#[Required]
	#[Translatable]
	public string $text;
}
