<?php declare(strict_types=1);

namespace Torr\Storyblok\Exception\Sync;

use Torr\Storyblok\Exception\StoryblokException;

final class ValidationFailedException extends \RuntimeException implements StoryblokException
{
}
