<?php declare(strict_types=1);

namespace Torr\Storyblok\Exception\Api;

use Torr\Storyblok\Exception\StoryblokException;

final class ApiRequestFailedException extends \RuntimeException implements StoryblokException
{
}
