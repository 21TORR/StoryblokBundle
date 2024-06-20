<?php declare(strict_types=1);

namespace Torr\Storyblok\Field\Data\Story;

use Torr\Storyblok\Field\Data\StoryReferenceData;

final class StoryReferenceList
{
	/**
	 * @param list<StoryReferenceData> $references
	 */
	public function __construct (
		public readonly array $references,
	) {}
}
