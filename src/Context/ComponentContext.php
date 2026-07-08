<?php declare(strict_types=1);

namespace Torr\Storyblok\Context;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Torr\Storyblok\Definition\Field\MappedField;
use Torr\Storyblok\Exception\Story\InvalidDataException;
use Torr\Storyblok\Image\ImageDimensionsExtractor;

/**
 * @final
 */
readonly class ComponentContext
{
	public ValidatorInterface $validator;
	public ImageDimensionsExtractor $imageDimensionsExtractor;

	/**
	 */
	public function __construct (
		?ImageDimensionsExtractor $imageDimensionsExtractor = null,
	)
	{
		// We don't use the validator from the DI container here,
		// as in debug it will be a traceable validator that logs
		// every call. As we are producing A TON of calls here
		// this will hugely increase the memory consumption.
		$this->validator = Validation::createValidator();
		$this->imageDimensionsExtractor = $imageDimensionsExtractor ?? new ImageDimensionsExtractor();
	}

	/**
	 * Ensures that the given data is valid
	 *
	 * @param string[]              $contentPathHierarchy The path to the given content element
	 * @param list<Constraint|null> $constraints
	 *
	 * @return void|never
	 *
	 * @throws InvalidDataException
	 */
	public function ensureDataIsValid (
		string $contentPath,
		array $storyData,
		?MappedField $field,
		array $contentPathHierarchy,
		array $constraints,
	) : void
	{
		$fieldValue = $storyData[$contentPath] ?? null;

		// filter all disabled constraints
		$constraints = array_filter($constraints);

		if (empty($constraints))
		{
			return;
		}

		$violations = $this->validator->validate($fieldValue, $constraints);

		if (\count($violations) > 0)
		{
			throw new InvalidDataException(
				\sprintf(
					"Invalid data found at '%s':\n%s",
					implode(" → ", $contentPathHierarchy),
					$violations,
				),
				$contentPathHierarchy,
				$field,
				$fieldValue,
				$violations,
			);
		}
	}

	/**
	 *
	 */
	public function normalizeOptionalString (?string $value) : ?string
	{
		if (null === $value)
		{
			return null;
		}

		// Normalize to null. Trim for checking, but don't trim data if is not empty, just to be sure.
		return "" !== trim($value)
			? $value
			: null;
	}
}
