<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Fixtures\Components\InvalidBaseDefinition;

use Torr\Storyblok\Definition\Mapping\NestedBlock;
use Torr\Storyblok\Definition\Mapping\StandaloneBlock;

/**
 * @final
 */
#[NestedBlock("test", "test")]
#[StandaloneBlock("test", "test")]
class BlokAndDocumentSet
{
}
