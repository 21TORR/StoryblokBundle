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
	#[Storyblok\TextField("Test")]
	public string $outer1;

	#[Storyblok\EmbeddedField("Test")]
	public FieldOrderOuterEmbed $indirect;

	#[Storyblok\TextField("Test")]
	public string $outer2;

	#[Storyblok\EmbeddedField("Test")]
	public FieldOrderInnerEmbed $direct;

	#[Storyblok\TextField("Test")]
	public string $outer3;

	#[Storyblok\EmbeddedField("Test", group: true)]
	public FieldOrderOuterGroupEmbed $group;

	#[Storyblok\TextField("Test")]
	public string $outer4;
}
