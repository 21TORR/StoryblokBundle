<?php declare(strict_types=1);

namespace Torr\Storyblok\ComponentFieldDefinition;

use Torr\Storyblok\Api\Manager\ComponentManager;
use Torr\Storyblok\Data\DataSerializationContext;

abstract class FieldDefinition
{
	public function __construct (
		private readonly ?string $label = null,
		private readonly ?int $position = null,
		private readonly mixed $defaultValue = null,
		private readonly ?string $description = null,
		private readonly bool $translatable = false,
		private readonly bool $required = false,
		private readonly ?string $regexp = null,
		private readonly ?bool $canSync = null,
		private readonly ?bool $isPreviewField = null,
	) {}

	//region Field Accessors
	public function getPosition () : ?int
	{
		return $this->position;
	}

	public function getDescription () : ?string
	{
		return $this->description;
	}

	public function isTranslatable () : bool
	{
		return $this->translatable;
	}

	public function isRequired () : bool
	{
		return $this->required;
	}

	public function getRegexp () : ?string
	{
		return $this->regexp;
	}

	public function isCanAsync () : ?bool
	{
		return $this->canSync;
	}

	public function isPreviewField () : ?bool
	{
		return $this->isPreviewField;
	}
	//endregion

	abstract public static function getType () : FieldType;

	/**
	 * Returns the API representation of the custom fields of this Component Field that differ from the base FieldDefinition
	 */
	abstract public function getSchemaDefinition (ComponentManager $componentManager) : array;

	final public function toApiData (DataSerializationContext $context) : array
	{
		return [
			"type" => static::getType()->value,
			"display_name" => $this->label,
			"pos" => $this->position,
			"default_value" => $this->defaultValue,
			"description" => $this->description,
			"translatable" => $this->translatable,
			"required" => $this->required,
			"regexp" => $this->regexp,
			"can_async" => $this->canSync,
			"preview_field" => $this->isPreviewField,
			...$this->getSchemaDefinition($context->getComponentManager()),
		];
	}
}
