<?php declare(strict_types=1);

namespace Torr\Storyblok\Field\Group;

use Symfony\Component\Validator\Constraints\Type;
use Torr\Storyblok\Context\ComponentContext;
use Torr\Storyblok\Field\Collection\FieldCollection;
use Torr\Storyblok\Field\Definition\AbstractField;
use Torr\Storyblok\Field\FieldDefinitionInterface;
use Torr\Storyblok\Field\NestedFieldDefinitionInterface;
use Torr\Storyblok\Visitor\DataVisitorInterface;

abstract class AbstractGroupingElement extends AbstractField implements NestedFieldDefinitionInterface
{
	private readonly FieldCollection $fieldCollection;

	/**
	 */
	public function __construct (
		string $label,
		/** @var array<string, FieldDefinitionInterface> $fields */
		array $fields,
	)
	{
		parent::__construct($label);
		$this->fieldCollection = new FieldCollection($fields);
	}

	/**
	 * @inheritDoc
	 */
	public function toManagementApiData (int $position, ) : array
	{
		return \array_replace(
			parent::toManagementApiData($position),
			[
				"keys" => \array_keys($this->fieldCollection->getRootFields()),
			],
		);
	}

	/**
	 * @inheritDoc
	 */
	public function getNestedFields () : array
	{
		return $this->fieldCollection->getRootFields();
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

		foreach ($this->fieldCollection->getTransformableFields() as $name => $field)
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
		?DataVisitorInterface $dataVisitor = null,
	) : array
	{
		\assert(null === $data || \is_array($data));
		$data ??= [];
		$transformed = [];

		foreach ($this->fieldCollection->getTransformableFields() as $name => $field)
		{
			$transformed[$name] = $field->transformData(
				$data[$name] ?? null,
				$context,
				$dataVisitor,
			);
		}

		return parent::transformData(
			$transformed,
			$context,
			$dataVisitor,
		);
	}
}
