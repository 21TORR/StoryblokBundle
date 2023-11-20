<?php declare(strict_types=1);

namespace Torr\Storyblok\Field\Definition;

use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Type;
use Torr\Storyblok\Context\ComponentContext;
use Torr\Storyblok\Field\Data\TableData;
use Torr\Storyblok\Field\FieldType;
use Torr\Storyblok\Visitor\DataVisitorInterface;

/**
 * The field for managing tables.
 *
 * Doesn't support the `no_translate` option, it is always disabled.
 */
final class TableField extends AbstractField
{
	/**
	 * @inheritDoc
	 */
	public function __construct (
		string $label,
		?string $defaultValue = null,
	)
	{
		parent::__construct($label, $defaultValue);
	}

	/**
	 * @inheritDoc
	 */
	protected function getInternalStoryblokType () : FieldType
	{
		return FieldType::Table;
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
				new Type("array"),
				new Collection([
					"thead" => new Type("array"),
					"tbody" => new Type("array"),
					"fieldtype" => new Choice(["table"]),
				]),
			],
		);
	}

	/**
	 * @param array{fieldtype: string, tbody: array, thead: array}|null $data
	 *
	 * @inheritDoc
	 */
	public function transformData (
		mixed $data,
		ComponentContext $context,
		array $fullData,
		?DataVisitorInterface $dataVisitor = null,
	) : ?TableData
	{
		$transformed = null;

		if (\is_array($data) && \is_array($data["thead"]) && \is_array($data["tbody"]))
		{
			$transformed = new TableData(
				thead: $data["thead"],
				tbody: $data["tbody"],
			);
		}

		$dataVisitor?->onDataVisit($this, $transformed);

		return $transformed;
	}
}
