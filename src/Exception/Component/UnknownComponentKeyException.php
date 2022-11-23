<?php declare(strict_types=1);

namespace Torr\Storyblok\Exception\Component;

use Torr\Storyblok\Exception\StoryblokException;

final class UnknownComponentKeyException extends \InvalidArgumentException implements StoryblokException
{
}
