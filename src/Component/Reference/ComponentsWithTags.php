<?php declare(strict_types=1);

namespace Torr\Storyblok\Component\Reference;

final class ComponentsWithTags
{
	/** @var string[] */
	public readonly array $tags;

	public function __construct (string ...$tags)
	{
		$this->tags = $tags;
	}
}
