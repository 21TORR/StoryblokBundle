<?php declare(strict_types=1);

namespace Torr\Storyblok\ComponentFieldDefinition\Field;

use Torr\Storyblok\Api\Manager\ComponentManager;
use Torr\Storyblok\ComponentFieldDefinition\FieldDefinition;
use Torr\Storyblok\ComponentFieldDefinition\FieldType;
use Torr\Storyblok\Data\StoryblokOptionsConfiguration;

class OptionFieldDefinition extends FieldDefinition
{
	//region Fields
	private StoryblokOptionsConfiguration $optionsConfig;
	//endregion

	public function __construct (
		StoryblokOptionsConfiguration $optionsConfig,
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

		$this->optionsConfig = $optionsConfig;
	}

	//region Field Accessors
	public function getOptionsConfig () : StoryblokOptionsConfiguration
	{
		return $this->optionsConfig;
	}
	//endregion

	// @inheritDoc
	public static function getType () : FieldType
	{
		return FieldType::Option;
	}


	// @inheritDoc
	public function getSchemaDefinition (ComponentManager $componentManager) : array
	{
		return $this->optionsConfig->getSchemaDefinition($componentManager);
	}
}
