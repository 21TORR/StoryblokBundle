<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Fixtures\Components\InvalidFields;

use Torr\Storyblok\Definition\Mapping\Blok;
use Torr\Storyblok\Definition\Mapping\IntegerField;
use Torr\Storyblok\Definition\Mapping\TextField;
use Torr\Storyblok\Story\Data\BlokStory;

/**
 * @final
 */
#[Blok("test", "test")]
class DuplicateField extends BlokStory
{
	#[TextField("field", "field")]
	#[IntegerField("field2", "field2")]
	public string $text;
}
