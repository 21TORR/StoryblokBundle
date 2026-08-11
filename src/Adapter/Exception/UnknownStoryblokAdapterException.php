<?php declare(strict_types=1);

namespace Torr\Storyblok\Adapter\Exception;

use Torr\Storyblok\Exception\StoryblokException;

final class UnknownStoryblokAdapterException extends StoryblokException
{
	/**
	 * @inheritDoc
	 */
	public function __construct (
		string $message,
		public readonly string $adapterKey,
		?\Throwable $previous = null,
	)
	{
		parent::__construct($message, 0, $previous);
	}
}
