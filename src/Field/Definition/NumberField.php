<?php declare(strict_types=1);

namespace Torr\Storyblok\Field\Definition;

use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Type;
use Torr\Storyblok\Context\ComponentContext;
use Torr\Storyblok\Field\FieldType;
use Torr\Storyblok\Visitor\DataVisitorInterface;

final class NumberField extends AbstractField
{
	/**
	 * @inheritDoc
	 */
	public function __construct (string $label, int|float|null $defaultValue = null)
	{
		parent::__construct($label, $defaultValue);
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
	public function validateData (ComponentContext $context, array $contentPath, mixed $data) : void
	{
		$context->ensureDataIsValid(
			$contentPath,
			$this,
			$data,
			[
				new NotNull(),
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
		?DataVisitorInterface $dataVisitor = null,
	) : int|float|null
	{
		\assert(null === $data || \is_string($data));

		if (null !== $data && "" !== $data)
		{
			$transformed = \str_contains($data, ".")
				? (float) $data
				: (int) $data;
		}
		else
		{
			$transformed = null;
		}

		return parent::transformData($transformed, $context, $dataVisitor);
	}
}
