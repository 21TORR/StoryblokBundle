<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Fixtures\Components\InvalidFields;

use Torr\Storyblok\Definition\Mapping\IntegerField;
use Torr\Storyblok\Definition\Mapping\NestedBlock;
use Torr\Storyblok\Definition\Mapping\TextField;
use Torr\Storyblok\Story\Data\NestedStory;

/**
 * @final
 */
#[NestedBlock("test", "test")]
class DuplicateField extends NestedStory
{
	#[TextField("field", key: "field")]
	#[IntegerField("field2", key: "field2")]
	public string $text;
}
