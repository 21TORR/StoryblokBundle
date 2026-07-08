<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Fixtures\Block;

use Torr\Storyblok\Content\RichText;
use Torr\Storyblok\Definition\Mapping as Storyblok;
use Torr\Storyblok\Story\Data\NestedStory;

/**
 * @final
 */
#[Storyblok\NestedBlock("rich-text-block", "RTE")]
class RichTextBlock extends NestedStory
{
	#[Storyblok\RichTextField("Content")]
	public RichText $content;

	#[Storyblok\TextField("background")]
	public string $background;

	#[Storyblok\TextField(label: "2. CTA", key: "secondCtaType")]
	public string $cta2;
}
