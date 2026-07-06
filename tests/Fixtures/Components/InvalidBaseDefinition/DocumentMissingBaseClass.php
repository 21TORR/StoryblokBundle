<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Fixtures\Components\InvalidBaseDefinition;

use Torr\Storyblok\Definition\Mapping\Document;

/**
 * @final
 */
#[Document(
	key: "document",
	label: "Document",
)]
class DocumentMissingBaseClass
{
}
