<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Fixtures\Components\FieldOrder;

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
	public string $outer1;

	#[Storyblok\EmbeddedField("test")]
	public FieldOrderOuterEmbed $indirect;

	#[Storyblok\TextField("Middle")]
	public string $outer2;

	#[Storyblok\EmbeddedField("test")]
	public FieldOrderInnerEmbed $direct;

	#[Storyblok\TextField("Last")]
	public string $outer3;
}
