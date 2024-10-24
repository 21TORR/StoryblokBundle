<?php declare(strict_types=1);

namespace Torr\Storyblok\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Torr\Storyblok\Component\AbstractComponent;
use Torr\Storyblok\Exception\Story\InvalidDataException;
use Torr\Storyblok\Field\FieldDefinitionInterface;

/**
 * @final
 */
class DataValidator
{
	public function __construct (
		private readonly ValidatorInterface $validator,
	) {}

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
		FieldDefinitionInterface|AbstractComponent|null $field,
		mixed $data,
		array $constraints,
	) : void
	{
		// filter all disabled constraints
		$constraints = array_filter($constraints);

		if (empty($constraints))
		{
			return;
		}

		$violations = $this->validator->validate($data, $constraints);

		if (\count($violations) > 0)
		{
			throw new InvalidDataException(
				\sprintf(
					"Invalid data found at '%s':\n%s",
					implode(" → ", $contentPath),
					$violations instanceof ConstraintViolationList
						? (string) $violations
						: "n/a",
				),
				$contentPath,
				$field,
				$data,
				$violations,
			);
		}
	}
}
