<?php declare(strict_types=1);

namespace Torr\Storyblok\Exception\Story;

use Symfony\Component\Validator\ConstraintViolationListInterface;
use Torr\Storyblok\Exception\StoryblokException;
use Torr\Storyblok\Field\FieldDefinitionInterface;

final class InvalidDataException extends \RuntimeException implements StoryblokException
{
	/**
	 */
	public function __construct (
		string $message,
		/** @var array<string> $path */
		public readonly ?array $path = null,
		public readonly ?FieldDefinitionInterface $field = null,
		public readonly mixed $data = null,
		public readonly ?ConstraintViolationListInterface $violations = null,
		?\Throwable $previous = null,
	)
	{
		parent::__construct($message, previous: $previous);
	}
}
