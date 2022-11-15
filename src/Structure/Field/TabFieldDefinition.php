<?php declare(strict_types=1);

namespace Torr\Storyblok\Structure\Field;

use Torr\Storyblok\Data\FieldType;
use Torr\Storyblok\Validator\DataValidator;

final class TabFieldDefinition extends FieldDefinition
{
	/**
	 * @param string[] $fieldNames
	 */
	public function __construct (
		string $label,
		array $fieldNames,
		?int $position = null,
		?string $description = null,
		bool $translatable = false,
	)
	{
		parent::__construct(
			type: FieldType::Tab,
			label: $label,
			position: $position,
			description: $description,
			translatable: $translatable,
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
