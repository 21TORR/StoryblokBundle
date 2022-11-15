<?php declare(strict_types=1);

namespace Torr\Storyblok\Structure\Field;

use Torr\Storyblok\Data\FieldType;
use Torr\Storyblok\Data\FileType;
use Torr\Storyblok\Validator\DataValidator;

final class AssetFieldDefinition extends FieldDefinition
{
	/**
	 * @param FileType[]|null $fileTypes
	 */
	public function __construct (
		public readonly ?array $fileTypes = null,
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
		$serializedFileTypes = null;

		if (null !== $fileTypes)
		{
			$serializedFileTypes = [];

			foreach ($fileTypes as $fileType)
			{
				$serializedFileTypes[] = $fileType->value;
			}
		}

		parent::__construct(
			type: FieldType::Asset,
			label: $label,
			position: $position,
			defaultValue: $defaultValue,
			description: $description,
			translatable: $translatable,
			required: $required,
			regexp: $regexp,
			canSync: $canSync,
			isPreviewField: $isPreviewField,
			additionalFieldData: [
				"filetypes" => $serializedFileTypes,
			],
		);
	}

	// @inheritDoc
	public function validateData (DataValidator $validator, array $path, mixed $data, ) : void
	{
		// @todo add implementation
	}
}
