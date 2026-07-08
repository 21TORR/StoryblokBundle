<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Fixtures\Components;

use Torr\Storyblok\Definition\Mapping\Document;
use Torr\Storyblok\Definition\Mapping\TextField;
use Torr\Storyblok\Story\Data\DocumentStory;

/**
 * @final
 */
#[Document("simple", "Simple Label")]
class Simple extends DocumentStory
{
	#[TextField("Text")]
	public ?string $text;
}
