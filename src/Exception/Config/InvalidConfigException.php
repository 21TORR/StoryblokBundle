<?php declare(strict_types=1);

namespace Torr\Storyblok\Exception\Config;

use Torr\Storyblok\Exception\StoryblokException;

final class InvalidConfigException extends \InvalidArgumentException implements StoryblokException
{
}
