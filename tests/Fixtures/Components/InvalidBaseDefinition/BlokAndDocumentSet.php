<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Fixtures\Components\InvalidBaseDefinition;

use Torr\Storyblok\Definition\Mapping\Blok;
use Torr\Storyblok\Definition\Mapping\Document;

/**
 * @final
 */
#[Blok("test", "test")]
#[Document("test", "test")]
class BlokAndDocumentSet
{
}
