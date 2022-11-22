<?php declare(strict_types=1);

namespace Torr\Storyblok\Structure\Field;

use Torr\Storyblok\Data\FieldType;
use Torr\Storyblok\Data\AssetFileType;
use Torr\Storyblok\Validator\DataValidator;

final class AssetField extends AbstractField
{
	/**
	 * @inheritDoc
	 */
	public function __construct (
		string $label,
		private readonly ?array $fileTypes,
		private readonly bool $allowMultiple = false,
		mixed $defaultValue = null,
	)
	{
		parent::__construct($label, $defaultValue);
	}

	/**
	 * @inheritDoc
	 */
	protected function getInternalStoryblokType () : FieldType
	{
		return $this->allowMultiple
			? FieldType::MultiAsset
			: FieldType::Asset;
	}

	/**
	 * @inheritDoc
	 */
	public function toManagementApiData (int $position,) : array
	{
		return \array_replace(
			parent::toManagementApiData($position),
			[
				"filetypes" => \array_map(
					static fn (AssetFileType $fileType) => $fileType->value,
					$this->fileTypes ?? AssetFileType::cases(),
				),
			],
		);
	}

	/**
	 * @inheritDoc
	 */
	public function validateData (DataValidator $validator, array $path, mixed $data) : void
	{
		// @todo add implementation
	}
}
