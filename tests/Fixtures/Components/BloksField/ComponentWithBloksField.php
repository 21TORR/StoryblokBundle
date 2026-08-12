<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Fixtures\Components\BloksField;

use Torr\Storyblok\Definition\Filter\ComponentFilter;
use Torr\Storyblok\Definition\Mapping\BloksField;
use Torr\Storyblok\Definition\Mapping\Component;
use Torr\Storyblok\Story\Data\Story;
use function Torr\Storyblok\Component\Filter\ComponentFilter;

/**
 * @final
 */
#[Component("with-bloks-field", "With Bloks Field")]
class ComponentWithBloksField extends Story
{
	#[BloksField(
		"Bloks",
		allowedComponentKeys: [
			"test",
			"abc",
		],
		allowedComponentTags: [
			"test",
		]
	)]
	public array $bloks;


	#[BloksField(
		"Bloks",
		allow: ComponentFilter::tags("test"),
	)]
	public array $bloks;
}
