<?php declare(strict_types=1);

namespace Torr\Storyblok\ComponentFieldDefinition\Field;

use Torr\Storyblok\Api\Manager\ComponentManager;
use Torr\Storyblok\ComponentFieldDefinition\FieldDefinition;
use Torr\Storyblok\ComponentFieldDefinition\FieldType;
use Torr\Storyblok\Data\StoryblokFileType;

final class MultiAssetFieldDefinition extends FieldDefinition
{
	//region Fields
	/** @var StoryblokFileType[]|null */
	private readonly ?array $fileTypes;
	//endregion

	/**
	 * @param StoryblokFileType[]|null $fileTypes
	 */
	public function __construct (
		?array $fileTypes = null,
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

		$this->fileTypes = $fileTypes;
	}

	//region Field Accessors
	/**
	 * @return StoryblokFileType[]|null
	 */
	public function getFileTypes () : ?array
	{
		return $this->fileTypes;
	}
	//endregion

	// @inheritDoc
	public static function getType () : FieldType
	{
		return FieldType::MultiAsset;
	}


	// @inheritDoc
	public function getSchemaDefinition (ComponentManager $componentManager) : array
	{
		$fileTypes = null;

		foreach (($this->fileTypes ?? []) as $fileType)
		{
			$fileTypes[] = $fileType->value;
		}

		return [
			"filetypes" => $fileTypes,
		];
	}
}
