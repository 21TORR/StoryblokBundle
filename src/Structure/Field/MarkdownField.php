<?php declare(strict_types=1);

namespace Torr\Storyblok\Structure\Field;

use Torr\Storyblok\Data\FieldType;
use Torr\Storyblok\Validator\DataValidator;

final class MarkdownField extends AbstractField
{
	/**
	 * @inheritDoc
	 */
	public function __construct (
		string $label,
		mixed $defaultValue = null,
		?string $description = null,
		private readonly bool $hasRichMarkdown = true,
		private readonly ?int $maxLength = null,
		private readonly bool $isRightToLeft = false,
	)
	{
		parent::__construct($label, $defaultValue);
	}

	/**
	 * @inheritDoc
	 */
	protected function getInternalStoryblokType () : FieldType
	{
		return FieldType::Markdown;
	}

	/**
	 * @inheritDoc
	 */
	public function toManagementApiData (int $position,) : array
	{
		return \array_replace(
			parent::toManagementApiData($position),
			[
				"rich_markdown" => $this->hasRichMarkdown,
				"rtl" => $this->isRightToLeft,
				"max_length" => $this->maxLength,
			],
		);
	}

	/**
	 * @inheritDoc
	 */
	public function validateData (DataValidator $validator, array $path, mixed $data) : void
	{
		// @todo add implementation
	}
}
