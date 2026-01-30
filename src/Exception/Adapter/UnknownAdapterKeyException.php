<?php declare(strict_types=1);

namespace Torr\Storyblok\Exception\Adapter;

use Torr\Storyblok\Exception\StoryblokException;

final class UnknownAdapterKeyException extends \InvalidArgumentException implements StoryblokException
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
