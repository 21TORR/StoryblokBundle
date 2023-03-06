<?php declare(strict_types=1);

namespace Torr\Storyblok\Field\Data;

final class StoryReferenceData
{
	public function __construct (
		public readonly array $uuids,
		public readonly string|\BackedEnum|null $dataMode,
	) {}
}
