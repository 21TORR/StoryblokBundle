<?php declare(strict_types=1);

namespace Torr\Storyblok\Structure\Field;

use Torr\Storyblok\Data\FieldType;
use Torr\Storyblok\Validator\DataValidator;

final class TableFieldDefinition extends FieldDefinition
{
	public function __construct (
		?string $label = null,
		?int $position = null,
		mixed $defaultValue = null,
		?string $description = null,
		bool $translatable = false,
		bool $required = false,
		?string $regexp = null,
		?bool $canSync = null,
		?bool $isPreviewField = null,
	)
	{
		parent::__construct(
			type: FieldType::Table,
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
	}

	/**
	 * @inheritDoc
	 */
	public function validateData (DataValidator $validator, array $path, mixed $data, ) : void
	{
		// @todo add implementation
	}
}
