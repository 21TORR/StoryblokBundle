<?php declare(strict_types=1);

namespace Torr\Storyblok\Mapping\Field;

use Torr\Storyblok\Field\FieldType;
use Torr\Storyblok\Validator\DataValidator;

abstract class AbstractField
{
	public function __construct (
		public readonly FieldType $internalStoryblokType,
		public readonly string $key,
		public readonly string $label,
		public readonly mixed $defaultValue = null,
	) {}


	/**
	 *
	 */
	public function generateManagementApiData () : array
	{
		return [
			"type" => $this->internalStoryblokType->value,
			"display_name" => $this->label,
			"default_value" => $this->defaultValue,
		];
	}

	/**
	 * Transforms the raw data from Storyblok to a sanitized format.
	 */
	public function transformRawData (mixed $data) : mixed
	{
		return $data;
	}


	/**
	 * Validates the given data
	 */
	public function validateData (
		array $contentPath,
		DataValidator $validator,
		mixed $data,
	) : void
	{
	}
}
