<?php declare(strict_types=1);

namespace Torr\Storyblok\Exception\Story;

use Symfony\Component\Validator\ConstraintViolationListInterface;
use Torr\Storyblok\Definition\Field\MappedField;
use Torr\Storyblok\Exception\StoryblokException;

final class InvalidDataException extends StoryblokException
{
	/**
	 */
	public function __construct (
		string $message,
		/** @var string[]|null $propertyHierarchy */
		public readonly ?array $propertyHierarchy = null,
		public readonly ?MappedField $field = null,
		public readonly mixed $fieldValue = null,
		public readonly ?ConstraintViolationListInterface $violations = null,
		?\Throwable $previous = null,
	)
	{
		parent::__construct($message, previous: $previous);
	}
}
