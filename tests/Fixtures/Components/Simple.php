<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Fixtures\Components;

use Torr\Storyblok\Definition\Mapping\Document;
use Torr\Storyblok\Definition\Mapping\TextField;
use Torr\Storyblok\Story\Data\DocumentStory;

/**
 * @final
 */
#[Document("simple", "simple")]
class Simple extends DocumentStory
{
	#[TextField("text", "Text")]
	public ?string $text;
}
