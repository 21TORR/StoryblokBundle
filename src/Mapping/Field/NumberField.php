<?php declare(strict_types=1);

namespace Torr\Storyblok\Mapping\Field;

use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Type;
use Torr\Storyblok\Exception\InvalidFieldConfigurationException;
use Torr\Storyblok\Field\FieldType;
use Torr\Storyblok\Hydrator\StoryHydrator;
use Torr\Storyblok\Data\Validator\DataValidator;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class NumberField extends AbstractField
{
	/**
	 */
	public function __construct (
		string $key,
		string $label,
		mixed $defaultValue = null,
		/**
		 * A field with 0 decimals will return an int, everything else a float
		 *
		 * @type positive-int
		 */
		private readonly int $numberOfDecimals = 0,
		private readonly int|float|null $minValue = null,
		private readonly int|float|null $maxValue = null,

		/**
		 * Only relevant for the UI: defines by how much the value will
		 * increase/decrease when clicking the step arrows
		 */
		private readonly int $numberOfSteps = 0,
	)
	{
		if ($this->numberOfDecimals < 0)
		{
			throw new InvalidFieldConfigurationException(\sprintf(
				"numberOfDecimals must be a positive integer, but is %d",
				$this->numberOfDecimals,
			));
		}

		parent::__construct(
			FieldType::Number,
			$key,
			$label,
			$defaultValue,
		);
	}

	/**
	 * @inheritDoc
	 */
	public function generateManagementApiData () : array
	{
		return \array_replace(parent::generateManagementApiData(), [
			"min_value" => $this->minValue,
			"max_value" => $this->maxValue,
			"decimals" => $this->numberOfDecimals,
			"steps" => $this->numberOfSteps,
		]);
	}

	/**
	 * @inheritDoc
	 */
	public function transformRawData (array $contentPath, mixed $data, StoryHydrator $hydrator) : int|float|null
	{
		if (null === $data)
		{
			return null;
		}

		\assert(\is_string($data));

		return 0 === $this->numberOfDecimals
			? (int) $data
			: (float) $data;
	}


	/**
	 * @inheritDoc
	 */
	public function validateData (array $contentPath, DataValidator $validator, mixed $data,) : void
	{
		$mustBeInt = 0 === $this->numberOfDecimals;

		$constraints = [
			new Type("string"),
			new Regex(
				$mustBeInt
					? "~^\d+$~"
					: "~^\d+(\\.\d+)?$~",
				message: $mustBeInt
					? "storyblok.field.number.must-be-integer"
					: "storyblok.field.number.must-be-float",
			)
		];

		if (null !== $this->minValue || null !== $this->maxValue)
		{
			$constraints[] = new Range(
				min: $this->minValue,
				max: $this->maxValue,
			);
		}

		$validator->ensureDataIsValid($contentPath, $data, $constraints);
	}


}
