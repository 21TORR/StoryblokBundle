<?php declare(strict_types=1);

namespace Torr\Storyblok\Field\Definition;

use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\AtLeastOneOf;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Type;
use Torr\Storyblok\Context\ComponentContext;
use Torr\Storyblok\Exception\InvalidFieldConfigurationException;
use Torr\Storyblok\Field\Choices\ChoicesInterface;
use Torr\Storyblok\Field\FieldType;
use Torr\Storyblok\Visitor\DataVisitorInterface;

final class ChoiceField extends AbstractField
{
	/**
	 */
	public function __construct (
		string $label,
		private readonly ChoicesInterface $choices,
		private readonly bool $allowMultiselect = false,
		private readonly int|string|\BackedEnum|null $defaultValue = null,
		private readonly ?int $minimumNumberOfOptions = null,
		private readonly ?int $maximumNumberOfOptions = null,
	)
	{
		parent::__construct($label, $this->defaultValue);

		if (!$this->allowMultiselect && (null !== $this->minimumNumberOfOptions || null !== $this->maximumNumberOfOptions))
		{
			throw new InvalidFieldConfigurationException(\sprintf(
				"Can't configure minimum or maximum amount of options for single-select choice.",
			));
		}

		if (
			null !== $this->minimumNumberOfOptions
			&& null !== $this->maximumNumberOfOptions
			&& $this->minimumNumberOfOptions > $this->maximumNumberOfOptions
		)
		{
			throw new InvalidFieldConfigurationException(\sprintf(
				"The minimum number of options value can't be higher than the maximum",
			));
		}
	}


	/**
	 * @inheritDoc
	 */
	protected function getInternalStoryblokType () : FieldType
	{
		return $this->allowMultiselect
			? FieldType::Options
			: FieldType::Option;
	}

	/**
	 * @inheritDoc
	 */
	protected function toManagementApiData () : array
	{
		return \array_replace(
			parent::toManagementApiData(),
			$this->choices->toManagementApiData(),
			[
				"default_value" => $this->defaultValue instanceof \BackedEnum
					? $this->defaultValue->value
					: $this->defaultValue,
				"min_options" => $this->minimumNumberOfOptions,
				"max_options" => $this->maximumNumberOfOptions,
			],
		);
	}

	/**
	 * @inheritDoc
	 */
	public function validateData (ComponentContext $context, array $contentPath, mixed $data, array $fullData) : void
	{
		if ($this->allowMultiselect)
		{
			$this->validateMultiSelect($context, $contentPath, $data);
		}
		else
		{
			$this->validateSingleSelect($context, $contentPath, $data);
		}
	}


	private function validateSingleSelect (ComponentContext $context, array $contentPath, mixed $data) : void
	{
		$context->ensureDataIsValid(
			$contentPath,
			$this,
			$data,
			[
				new AtLeastOneOf([
					new Type("string"),
					new Type("int"),
				]),
				$this->required
					? new NotBlank()
					: null,
			],
		);

		\assert(\is_int($data) || \is_string($data));

		if (\is_string($data))
		{
			$data = $context->normalizeOptionalString($data);
		}

		$choicesConstraints = $this->choices->getValidationConstraints(false);

		if (null !== $data && !empty($choicesConstraints))
		{
			$context->ensureDataIsValid(
				$contentPath,
				$this,
				$data,
				$choicesConstraints,
			);
		}
	}


	private function validateMultiSelect (ComponentContext $context, array $contentPath, mixed $data) : void
	{
		$context->ensureDataIsValid(
			$contentPath,
			$this,
			$data,
			[
				new Type("array"),
				new All([
					new NotNull(),
					new AtLeastOneOf([
						new Type("string"),
						new Type("int"),
					]),
				]),
			],
		);

		\assert(\is_array($data));

		if ($this->required || null !== $this->minimumNumberOfOptions || null !== $this->maximumNumberOfOptions)
		{
			$context->ensureDataIsValid(
				$contentPath,
				$this,
				$data,
				[
					new Count(
						min: $this->minimumNumberOfOptions ?? ($this->required ? 1 : null),
						max: $this->maximumNumberOfOptions,
						minMessage: "At least {{ limit }} option(s) must be selected.",
						maxMessage: "You cannot specify more than {{ limit }} options.",
					),
				],
			);
		}

		$choicesConstraints = $this->choices->getValidationConstraints(true);

		if (!empty($choicesConstraints))
		{
			$context->ensureDataIsValid(
				$contentPath,
				$this,
				$data,
				$choicesConstraints,
			);
		}
	}

	/**
	 * @inheritDoc
	 */
	public function transformData (
		mixed $data,
		ComponentContext $context,
		array $fullData,
		?DataVisitorInterface $dataVisitor = null,
	) : mixed
	{
		\assert(null === $data || \is_array($data) || \is_int($data) || \is_string($data));

		if (\is_string($data))
		{
			$data = $context->normalizeOptionalString($data);
		}

		$transformed = null !== $data
			? $this->choices->transformData($context, $data)
			: null;

		$dataVisitor?->onDataVisit($this, $transformed);
		return $transformed;
	}
}
