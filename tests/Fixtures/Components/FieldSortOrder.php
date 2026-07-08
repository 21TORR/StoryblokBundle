<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Fixtures\Components;

use Tests\Torr\Storyblok\Fixtures\Components\Embed\EmbeddedValue;
use Torr\Storyblok\Definition\Mapping as Storyblok;
use Torr\Storyblok\Story\Data\Story;

/**
 * @final
 */
#[Storyblok\Component(
	key: "field-sort-order",
	label: "Field Sort Order",
)]
class FieldSortOrder extends Story
{
	#[Storyblok\TextField("First")]
	public string $first;

	#[Storyblok\EmbeddedField("test", key: "nested_")]
	public EmbeddedValue $embed;

	#[Storyblok\TextField("Last")]
	public string $last;
}
