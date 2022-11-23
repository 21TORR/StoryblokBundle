<?php declare(strict_types=1);

namespace Torr\Storyblok\Field\Definition;

use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Type;
use Torr\Storyblok\Context\StoryblokContext;
use Torr\Storyblok\Field\FieldType;
use Torr\Storyblok\Validator\DataValidator;
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
	public function validateData (DataValidator $validator, array $path, mixed $data) : void
	{
		$validator->ensureDataIsValid(
			$path,
			$this,
			$data,
			[
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
		StoryblokContext $dataContext,
		?DataVisitorInterface $dataVisitor = null,
	) : mixed
	{
		if (\is_string($data) && "" !== $data)
		{
			$transformed = \str_contains($data, ".")
				? (float) $data
				: (int) $data;
		}
		else
		{
			$transformed = null;
		}

		return parent::transformData($transformed, $dataContext, $dataVisitor);
	}
}
