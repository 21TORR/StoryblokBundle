<?php declare(strict_types=1);

namespace Torr\Storyblok\Exception;

/**
 * Could not validate Storyblok webhook due to missing configuration data.
 */
class CouldNotValidateWebhookException extends \RuntimeException implements StoryblokExceptionInterface
{
}
