<?php declare(strict_types=1);

namespace Torr\Storyblok\ComponentFieldDefinition\Field;

use Torr\Storyblok\Api\Manager\ComponentManager;
use Torr\Storyblok\ComponentFieldDefinition\FieldDefinition;
use Torr\Storyblok\ComponentFieldDefinition\FieldType;

final class MarkdownFieldDefinition extends FieldDefinition
{
	//region Fields
	private readonly bool $hasRichMarkdown;
	private readonly bool $isRightToLeft;
	private readonly ?int $maxLength;
	//endregion

	public function __construct (
		bool $hasRichMarkdown = false,
		bool $isRightToLeft = false,
		?int $maxLength = null,
		?int $position = null,
		mixed $defaultValue = null,
		?string $label = null,
		?string $description = null,
		bool $translatable = false,
		bool $required = false,
		?string $regexp = null,
		?bool $canSync = null,
		bool $isPreviewField = false,
	)
	{
		parent::__construct(
			label: $label,
			position: $position,
			defaultValue: $defaultValue,
			description: $description,
			translatable: $translatable,
			required: $required,
			regexp: $regexp,
			canSync: $canSync,
			isPreviewField: $isPreviewField,
		);

		$this->hasRichMarkdown = $hasRichMarkdown;
		$this->isRightToLeft = $isRightToLeft;
		$this->maxLength = $maxLength;
	}

	//region Field Accessors
	public function isHasRichMarkdown () : bool
	{
		return $this->hasRichMarkdown;
	}

	public function isRightToLeft () : bool
	{
		return $this->isRightToLeft;
	}

	public function getMaxLength () : ?int
	{
		return $this->maxLength;
	}
	//endregion

	// @inheritDoc
	public static function getType () : FieldType
	{
		return FieldType::Markdown;
	}


	// @inheritDoc
	public function getSchemaDefinition (ComponentManager $componentManager) : array
	{
		return [
			"rich_markdown" => $this->hasRichMarkdown,
			"rtl" => $this->isRightToLeft,
			"max_length" => $this->maxLength,
		];
	}
}
