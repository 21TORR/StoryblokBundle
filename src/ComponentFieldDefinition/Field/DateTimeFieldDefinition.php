<?php declare(strict_types=1);

namespace Torr\Storyblok\ComponentFieldDefinition\Field;

use Torr\Storyblok\Api\Manager\ComponentManager;
use Torr\Storyblok\ComponentFieldDefinition\FieldDefinition;
use Torr\Storyblok\ComponentFieldDefinition\FieldType;

final class DateTimeFieldDefinition extends FieldDefinition
{
	//region Fields
	private readonly ?bool $disableTime;
	//endregion

	public function __construct (
		?bool $disableTimeSelection = null,
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

		$this->disableTime = $disableTimeSelection;
	}

	//region Field Accessors
	public function getDisableTime () : ?bool
	{
		return $this->disableTime;
	}
	//endregion

	// @inheritDoc
	public static function getType () : FieldType
	{
		return FieldType::DateTime;
	}


	// @inheritDoc
	public function getSchemaDefinition (ComponentManager $componentManager) : array
	{
		return [
			"disable_time" => $this->disableTime,
		];
	}
}
