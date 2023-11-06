<?php declare(strict_types=1);

namespace Torr\Storyblok\Mapping\Field;

use Torr\Storyblok\Field\FieldType;
use Torr\Storyblok\Hydrator\StoryHydrator;
use Torr\Storyblok\Data\Validator\DataValidator;
use function Symfony\Component\String\u;

abstract class AbstractField
{
	public function __construct (
		public readonly FieldType $internalStoryblokType,
		public readonly string $key,
		public readonly ?string $label = null,
		public readonly mixed $defaultValue = null,
	) {}


	/**
	 *
	 */
	public function generateManagementApiData () : array
	{
		return [
			"type" => $this->internalStoryblokType->value,
			"display_name" => $this->getLabel(),
			"default_value" => $this->defaultValue,
		];
	}

	/**
	 *
	 */
	public function getLabel () : string
	{
		return $this->label ?? u($this->key)
			->title()
			->toString();
	}

	/**
	 * Transforms the raw data from Storyblok to a sanitized format.
	 */
	public function transformRawData (
		array $contentPath,
		mixed $data,
		StoryHydrator $hydrator,
	) : mixed
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
	{}
}
