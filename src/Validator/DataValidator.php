<?php declare(strict_types=1);

namespace Torr\Storyblok\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Torr\Storyblok\Definition\Field\MappedField;
use Torr\Storyblok\Exception\Story\InvalidDataException;

/**
 * @final
 */
readonly class DataValidator
{
	public ValidatorInterface $validator;

	/**
	 */
	public function __construct ()
	{
		// We don't use the validator from the DI container here,
		// as in debug it will be a traceable validator that logs
		// every call. As we are producing A TON of calls here
		// this will hugely increase the memory consumption.
		$this->validator = Validation::createValidator();
	}

	/**
	 * Ensures that the given data is valid
	 *
	 * @param string[]              $contentPath The path to the given content element
	 * @param list<Constraint|null> $constraints
	 *
	 * @return void|never
	 *
	 * @throws InvalidDataException
	 */
	public function ensureDataIsValid (
		array $contentPath,
		?MappedField $field,
		array $storyData,
		array $constraints,
	) : void
	{
		// filter all disabled constraints
		$constraints = array_filter($constraints);

		if (empty($constraints))
		{
			return;
		}

		$violations = $this->validator->validate($storyData, $constraints);

		if (\count($violations) > 0)
		{
			throw new InvalidDataException(
				\sprintf(
					"Invalid data found at '%s':\n%s",
					implode(" → ", $contentPath),
					$violations,
				),
				$contentPath,
				$field,
				$storyData,
				$violations,
			);
		}
	}
}
