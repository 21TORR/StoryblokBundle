<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Fixtures\Components\InvalidBaseDefinition;

use Torr\Storyblok\Definition\Mapping\NestedBlock;

/**
 * @final
 */
#[NestedBlock(
	key: "blok",
	label: "Blok",
)]
class BlokMissingBaseClass
{
}
