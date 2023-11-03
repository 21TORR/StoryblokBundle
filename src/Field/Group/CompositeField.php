<?php declare(strict_types=1);

namespace Torr\Storyblok\Field\Group;

use Symfony\Component\String\Slugger\AsciiSlugger;
use Torr\Storyblok\Context\ComponentContext;
use Torr\Storyblok\Field\FieldDefinition;
use Torr\Storyblok\Field\FieldType;
use Torr\Storyblok\Visitor\DataVisitorInterface;

abstract class CompositeField extends AbstractGroupingElement
{
	private readonly string $prefix;

	/**
	 */
	public function __construct (
		string $label,
		?string $prefix = null,
	)
	{
		if (null === $prefix)
		{
			$prefix = (new AsciiSlugger())
				->slug($label)
				->replace("-", "_")
				->toString();
		}

		$this->prefix = \strtolower(\rtrim($prefix, "_")) . "_";
		$fields = [];

		foreach ($this->configureFields() as $name => $fieldConfig)
		{
			$fields[$this->prefix . $name] = $fieldConfig;
		}

		parent::__construct($label, $fields);
	}

	/**
	 * Configures the fields
	 *
	 * @return array<string, FieldDefinition>
	 */
	abstract protected function configureFields () : array;

	/**
	 * @inheritDoc
	 */
	protected function getInternalStoryblokType () : FieldType
	{
		return FieldType::Section;
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
		$transformed = [];

		foreach ($this->fields as $name => $fieldDefinition)
		{
			$unprefixedName = \preg_replace("~^(" . \preg_quote($this->prefix, "~") . ")~", "", $name);

			$transformed[$unprefixedName] = $fieldDefinition->transformData(
				$fullData[$name] ?? null,
				$context,
				$fullData,
				$dataVisitor,
			);
		}

		$dataVisitor?->onDataVisit($this, $transformed);
		return $transformed;
	}

	/**
	 * @inheritDoc
	 */
	public function validateData (ComponentContext $context, array $contentPath, mixed $data, array $fullData) : void
	{
		foreach ($this->fields as $name => $field)
		{
			$field->validateData(
				$context,
				[
					...$contentPath,
					\sprintf("Field(%s)", $name),
				],
				$fullData[$name] ?? null,
				$fullData,
			);
		}
	}
}
