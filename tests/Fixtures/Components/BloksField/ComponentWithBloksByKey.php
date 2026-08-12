<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Fixtures\Components\BloksField;

use Torr\Storyblok\Definition\Filter\ComponentFilter;
use Torr\Storyblok\Definition\Mapping\BloksField;
use Torr\Storyblok\Definition\Mapping\Component;
use Torr\Storyblok\Story\Data\Story;

/**
 * @final
 */
#[Component("with-bloks-field", "With Bloks Field")]
class ComponentWithBloksByKey extends Story
{
	#[BloksField(
		"Bloks",
		allow: new ComponentFilter(keys: ["simple"]),
	)]
	public array $bloks;
}
