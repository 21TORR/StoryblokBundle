<?php declare(strict_types=1);

namespace Torr\Storyblok\Field\Definition;

use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Type;
use Torr\Storyblok\Context\ComponentContext;
use Torr\Storyblok\Exception\InvalidFieldConfigurationException;
use Torr\Storyblok\Field\FieldType;
use Torr\Storyblok\Visitor\DataVisitorInterface;

final class NumberField extends AbstractField
{
	/**
	 * @inheritDoc
	 */
	public function __construct (
		string $label,
		int|float|null $defaultValue = null,
		private readonly bool $exportTranslation = false,
		private readonly float|int|null $minValue = null,
		private readonly float|int|null $maxValue = null,
		private readonly ?int $decimals = null,
		private readonly float|int|null $steps = null,
	)
	{
		parent::__construct(
			label: $label,
			defaultValue: null !== $defaultValue
				// Storyblok needs these values as string, so that they are handled properly
				? (string) $defaultValue
				: null,
		);

		if (null !== $this->maxValue && null !== $this->minValue && null !== $this->steps)
		{
			$difference = $this->maxValue - $this->minValue;

			if (fmod((float) $difference, (float) $this->steps) > 0)
			{
				throw new InvalidFieldConfigurationException(\sprintf(
					"Invalid number field config: the max value '%s' should be min value '%s' + a multiple of steps '%s'",
					$this->maxValue,
					$this->minValue,
					$this->steps,
				));
			}
		}

		if (null !== $this->decimals && $this->decimals < 0)
		{
			throw new InvalidFieldConfigurationException(\sprintf(
				"Invalid number field config: decimals '%s' must be greater or equal to 0",
				$this->decimals,
			));
		}
	}

	/**
	 * @inheritDoc
	 */
	protected function getInternalStoryblokType () : FieldType
	{
		return FieldType::Number;
	}

	/**
	 * @inheritDoc
	 */
	protected function toManagementApiData () : array
	{
		return array_replace(
			parent::toManagementApiData(),
			[
				"no_translate" => !$this->exportTranslation,
				...array_filter([
					"min_value" => $this->minValue,
					"max_value" => $this->maxValue,
					"decimals" => $this->decimals,
					"steps" => $this->steps,
				], static fn ($value) => null !== $value),
			],
		);
	}

	/**
	 * @inheritDoc
	 */
	public function validateData (ComponentContext $context, array $contentPath, mixed $data, array $fullData) : void
	{
		$context->ensureDataIsValid(
			$contentPath,
			$this,
			$data,
			[
				!$this->allowMissingData && $this->required ? new NotNull() : null,
				// numbers are always passed as strings
				new Type("string"),
				new Regex("~^\\d+(\\.\\d+)?$~"),
			],
		);
	}

	/**
	 * @inheritDoc
	 */
	public function transformData (
		mixed $data,
		ComponentContext $context,
		array $fullData,
		?DataVisitorInterface $dataVisitor = null,
	) : int|float|null
	{
		\assert(null === $data || \is_string($data));

		if (null !== $data && "" !== $data)
		{
			$transformed = str_contains($data, ".")
				? (float) $data
				: (int) $data;
		}
		else
		{
			$transformed = null;
		}

		$dataVisitor?->onDataVisit($this, $transformed);

		return $transformed;
	}
}
