<?php declare(strict_types=1);

namespace Torr\Storyblok\Exception\Story;

use Torr\Storyblok\Exception\StoryblokException;

final class UnknownFieldException extends \InvalidArgumentException implements StoryblokException
{
}
