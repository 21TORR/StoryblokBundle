<?php declare(strict_types=1);

namespace Torr\Storyblok\ComponentFieldDefinition\Field;

use Torr\Storyblok\Api\Manager\ComponentManager;
use Torr\Storyblok\ComponentFieldDefinition\FieldDefinition;
use Torr\Storyblok\ComponentFieldDefinition\FieldType;

final class TabFieldDefinition extends FieldDefinition
{
	//region Fields
	/** @var string[] */
	private readonly array $fieldNames;
	//endregion

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
			label: $label,
			position: $position,
			description: $description,
			translatable: $translatable,
		);

		$this->fieldNames = $fieldNames;
	}

	//region Field Accessors
	public function getFieldNames () : array
	{
		return $this->fieldNames;
	}
	//endregion

	public static function getType () : FieldType
	{
		return FieldType::Tab;
	}


	/**
	 * @inheritDoc
	 */
	public function getSchemaDefinition (ComponentManager $componentManager) : array
	{
		return [
			"keys" => $this->fieldNames,
		];
	}
}
