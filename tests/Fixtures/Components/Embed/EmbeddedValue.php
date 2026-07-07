<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Fixtures\Components\Embed;

use Torr\Storyblok\Definition\Mapping as Storyblok;

/**
 * @final
 */
class EmbeddedValue
{
	#[Storyblok\TextField(label: "Label", key: "label")]
	public string $label;

	#[Storyblok\TextField(label: "Label", key: "link")]
	public string $link;
}
