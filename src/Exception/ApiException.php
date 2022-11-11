<?php declare(strict_types=1);

namespace Torr\Storyblok\Exception;

/**
 * The API has returned an unexpected error.
 */
class ApiException extends \RuntimeException implements StoryblokExceptionInterface
{
}
