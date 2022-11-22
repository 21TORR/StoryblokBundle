<?php declare(strict_types=1);

namespace Torr\Storyblok\Exception\Sync;

use Torr\Storyblok\Exception\StoryblokException;

final class SyncFailedException extends \RuntimeException implements StoryblokException
{
}
