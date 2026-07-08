<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Fixtures\Components\InvalidBaseDefinition;

use Torr\Storyblok\Definition\Mapping\Component;

/**
 * @final
 */
#[Component(
	key: "blok",
	label: "Blok",
)]
class MissingBaseClass
{
}
