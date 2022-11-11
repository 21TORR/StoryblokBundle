<?php declare(strict_types=1);

namespace Torr\Storyblok\Exception;

/**
 * The field's name is one of the illegal/reserved names, that can't be used without breaking Storyblok.
 */
class IllegalFieldNameException extends \RuntimeException implements StoryblokExceptionInterface
{
}
