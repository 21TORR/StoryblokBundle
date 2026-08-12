<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Fixtures\Components\Tags;

use Torr\Storyblok\Definition\Mapping\Component;
use Torr\Storyblok\Definition\Mapping\TextField;
use Torr\Storyblok\Story\Data\Story;

/**
 * @final
 */
#[Component(
	key: "with-tag-bc",
	label: "With Tag BC",
	tags: ["b", "c"],
)]
class ComponentWithTagBC extends Story
{
	#[TextField("Headline")]
	public ?string $headline;
}
