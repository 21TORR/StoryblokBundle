<?php declare(strict_types=1);

namespace Torr\Storyblok\Structure\Field;

use Torr\Storyblok\Data\FieldType;
use Torr\Storyblok\Validator\DataValidator;

final class SectionFieldDefinition extends FieldDefinition
{
	/**
	 * @param string[] $fieldNames
	 */
	public function __construct (
		string $label,
		public readonly array $fieldNames,
		?int $position = null,
		?string $description = null,
		bool $translatable = false,
		bool $required = false,
		?string $regexp = null,
		?bool $canSync = null,
		bool $isPreviewField = false,
	)
	{
		parent::__construct(
			type: FieldType::Section,
			label: $label,
			position: $position,
			description: $description,
			translatable: $translatable,
			required: $required,
			regexp: $regexp,
			canSync: $canSync,
			isPreviewField: $isPreviewField,
			additionalFieldData: [
				"keys" => $fieldNames,
			],
		);
	}

	/**
	 * @inheritDoc
	 */
	public function validateData (DataValidator $validator, array $path, mixed $data, ) : void
	{
		// @todo add implementation
	}
}
