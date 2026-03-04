<?php declare(strict_types=1);

namespace Torr\Storyblok\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Torr\Cli\Console\Style\TorrStyle;
use Torr\Storyblok\Adapter\AbstractStoryblokAdapter;

/**
 * @final
 */
class StoryblokDefinitionsSyncedEvent extends Event
{
	/**
	 */
	public function __construct (
		public readonly TorrStyle $io,
		public readonly AbstractStoryblokAdapter $adapter,
	) {}
}
