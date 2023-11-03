<?php declare(strict_types=1);

namespace Torr\Storyblok\Validator;

use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Torr\Storyblok\Component\AbstractComponent;
use Torr\Storyblok\Exception\Story\InvalidDataException;
use Torr\Storyblok\Field\FieldDefinition;

/**
 * @final
 */
class DataValidator
{
	public function __construct (
		private readonly ValidatorInterface $validator,
		private readonly LoggerInterface $logger,
	) {}


	/**
	 * Ensures that the given data is valid
	 *
	 * @param string[]               $contentPath The path to the given content element
	 * @param array<Constraint|null> $constraints
	 *
	 * @throws InvalidDataException
	 *
	 * @return void|never
	 */
	public function ensureDataIsValid (
		array $contentPath,
		mixed $data,
		array $constraints,
	) : void
	{
		// filter all disabled constraints
		$constraints = \array_filter($constraints);

		if (empty($constraints))
		{
			return;
		}

		$violations = $this->validator->validate($data, $constraints);

		if (\count($violations) > 0)
		{
			$formattedPath = \implode(" → ", $contentPath);
			$formattedViolations = $violations instanceof ConstraintViolationList
				? (string) $violations
				: "n/a";

			$this->logger->error("Storyblok: Invalid data found at {path}", [
				"path" => $formattedPath,
				"violations" => $formattedViolations,
				"data" => $data,
			]);

			throw new InvalidDataException(
				\sprintf(
					"Invalid data found at '%s':\n%s",
					$formattedPath,
					$formattedViolations,
				),
				$contentPath,
				$data,
				$violations,
			);
		}
	}
}
