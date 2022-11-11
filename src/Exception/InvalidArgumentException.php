<?php declare(strict_types=1);

namespace Torr\Storyblok\Exception;

/**
 * Invalid or insufficient parameters have been provided.
 */
class InvalidArgumentException extends \RuntimeException implements StoryblokExceptionInterface
{
}
