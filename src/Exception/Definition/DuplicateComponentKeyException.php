<?php declare(strict_types=1);

namespace Torr\Storyblok\Exception\Definition;

use Torr\Storyblok\Exception\StoryblokException;

/**
 * Exception is thrown when two components with the same key are registered
 */
final class DuplicateComponentKeyException extends \InvalidArgumentException implements StoryblokException
{
}
