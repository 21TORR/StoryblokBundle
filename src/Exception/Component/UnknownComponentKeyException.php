<?php declare(strict_types=1);

namespace Torr\Storyblok\Exception\Component;

use Torr\Storyblok\Exception\StoryblokException;

final class UnknownComponentKeyException extends \InvalidArgumentException implements StoryblokException
{
	/**
	 * @inheritDoc
	 */
	public function __construct (
		string $message,
		public readonly string $componentKey,
		?\Throwable $previous = null,
	)
	{
		parent::__construct($message, 0, $previous);
	}

}
