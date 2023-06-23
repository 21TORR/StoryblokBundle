<?php declare(strict_types=1);

namespace Torr\Storyblok\Field\Definition;

use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\AtLeastOneOf;
use Symfony\Component\Validator\Constraints\IsTrue;
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
	)
	{
		parent::__construct($label, $this->defaultValue);

		if (
			(\is_int($this->defaultValue) || \is_string($this->defaultValue))
			&& !$this->choices->isValidData($this->defaultValue)
		)
		{
			throw new InvalidFieldConfigurationException(\sprintf(
				"Invalid default value %s in choice",
				\get_debug_type($this->defaultValue),
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
			],
		);
	}

	/**
	 * @inheritDoc
	 */
	public function validateData (ComponentContext $context, array $contentPath, mixed $data, array $fullData) : void
	{
		$allowedValueTypeConstraints = new AtLeastOneOf([
			new Type("string"),
			new Type("int"),
		]);

		$context->ensureDataIsValid(
			$contentPath,
			$this,
			$data,
			[
				$this->allowMultiselect
					? new All([new NotNull(), $allowedValueTypeConstraints])
					: $allowedValueTypeConstraints,
			],
		);

		\assert(\is_array($data) || \is_int($data) || \is_string($data));

		if (\is_string($data))
		{
			$data = $context->normalizeOptionalString($data);
		}

		if (null !== $data)
		{
			$context->ensureDataIsValid(
				$contentPath,
				$this,
				$this->choices->isValidData($data, $context),
				[
					new IsTrue(),
				],
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
