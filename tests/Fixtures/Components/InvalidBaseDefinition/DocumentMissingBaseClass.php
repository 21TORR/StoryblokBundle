<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Fixtures\Components\InvalidBaseDefinition;

use Torr\Storyblok\Definition\Mapping\StandaloneBlock;

/**
 * @final
 */
#[StandaloneBlock(
	key: "document",
	label: "Document",
)]
class DocumentMissingBaseClass
{
}
