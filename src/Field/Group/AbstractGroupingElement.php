<?php declare(strict_types=1);

namespace Torr\Storyblok\Field\Group;

use Symfony\Component\Validator\Constraints\Type;
use Torr\Storyblok\Context\ComponentContext;
use Torr\Storyblok\Field\Data\Helper\InlinedTransformedData;
use Torr\Storyblok\Field\Definition\AbstractField;
use Torr\Storyblok\Field\FieldDefinitionInterface;
use Torr\Storyblok\Field\NestedFieldDefinitionInterface;
use Torr\Storyblok\Management\ManagementApiData;
use Torr\Storyblok\Visitor\DataVisitorInterface;

abstract class AbstractGroupingElement extends AbstractField implements NestedFieldDefinitionInterface
{
	/**
	 * @param array<string, FieldDefinitionInterface> $fields
	 */
	public function __construct (
		string $label,
		/** @var array<string, FieldDefinitionInterface> $fields */
		protected readonly array $fields,
	)
	{
		parent::__construct($label);
	}

	/**
	 * @inheritDoc
	 */
	public function registerManagementApiData (string $key, ManagementApiData $managementApiData) : void
	{
		/** @var array<string, mixed> $fieldConfig */
		$fieldConfig = \array_replace(
			$this->toManagementApiData(),
			[
				"keys" => \array_keys($this->fields),
			],
		);
		unset($fieldConfig["required"], $fieldConfig["regexp"]);
		$managementApiData->registerField($key, $fieldConfig);

		foreach ($this->fields as $fieldName => $field)
		{
			$field->registerManagementApiData($fieldName, $managementApiData);
		}
	}

	/**
	 * @inheritDoc
	 */
	public function getNestedFields () : array
	{
		return $this->fields;
	}

	/**
	 * @inheritDoc
	 */
	public function validateData (ComponentContext $context, array $contentPath, mixed $data) : void
	{
		$context->ensureDataIsValid(
			$contentPath,
			$this,
			$data,
			[
				new Type("array"),
			],
		);

		foreach ($this->fields as $name => $field)
		{
			$fieldData = $data[$name] ?? null;

			$field->validateData(
				$context,
				[
					...$contentPath,
					\sprintf("Field(%s)", $name),
				],
				$fieldData,
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
	) : array|InlinedTransformedData
	{
		\assert(null === $data || \is_array($data));
		$data ??= [];
		$transformed = [];

		foreach ($this->fields as $name => $field)
		{
			$transformedFieldData = $field->transformData(
				$data[$name] ?? null,
				$context,
				$fullData,
				$dataVisitor,
			);

			if ($transformedFieldData instanceof InlinedTransformedData)
			{
				$transformed = [
					...$transformed,
					...$transformedFieldData->data,
				];
			}
			else
			{
				$transformed[$name] = $transformedFieldData;
			}
		}

		$dataVisitor?->onDataVisit($this, $transformed);
		return new InlinedTransformedData($transformed);
	}

	/**
	 * @inheritDoc
	 */
	public function enableValidation (
		bool $required = true,
		?string $regexp = null,
		bool $allowMissingData = false,
	) : static
	{
		foreach ($this->fields as $rootField)
		{
			if ($rootField instanceof AbstractField)
			{
				$rootField->enableValidation($required, $regexp, $allowMissingData);
			}
		}

		return $this;
	}
}
