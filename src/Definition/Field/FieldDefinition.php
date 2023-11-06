<?php declare(strict_types=1);

namespace Torr\Storyblok\Definition\Field;

use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Torr\Storyblok\Exception\Story\InvalidDataException;
use Torr\Storyblok\Mapping\Field\AbstractField;
use Torr\Storyblok\Mapping\FieldAttribute\FieldAttributeInterface;
use Torr\Storyblok\Data\Validator\DataValidator;

final readonly class FieldDefinition
{
	/**
	 */
	public function __construct (
		public AbstractField $field,
		public string $property,
		/** @var FieldAttributeInterface[] */
		private array $attributes = [],
	) {}

	/**
	 *
	 */
	public function generateManagementApiData () : array
	{
		$data = $this->field->generateManagementApiData();

		foreach ($this->attributes as $attribute)
		{
			foreach ($attribute->managementApiData as $key => $value)
			{
				$data[$key] = $value;
			}
		}

		return $data;
	}


	/**
	 * Validates the given data for the field
	 *
	 * @throws InvalidDataException
	 */
	public function validateData (
		array $contentPath,
		DataValidator $validator,
		mixed $data,
	) : void
	{
		$contentPath[] = \sprintf(
			$this->field->key !== $this->property
				? "%s (%s)"
				: "%s",
			$this->property,
			$this->field->key,
		);
		$constraints = [];

		foreach ($this->attributes as $attribute)
		{
			foreach ($attribute->getValidationConstraints() as $constraint)
			{
				$constraints[] = $constraint;
			}
		}

		// validate attributes first
		$validator->ensureDataIsValid(
			$contentPath,
			$data,
			$constraints,
		);

		$this->field->validateData(
			$contentPath,
			$validator,
			$data,
		);
	}
}
