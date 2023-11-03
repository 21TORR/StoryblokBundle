<?php declare(strict_types=1);

namespace Torr\Storyblok\Mapping\Field;

use Torr\Storyblok\Field\FieldType;

abstract class AbstractField
{
	public function __construct (
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
			"type" => $this->getInternalStoryblokType()->value,
			"display_name" => $this->label,
			"default_value" => $this->defaultValue,
		];
	}

	/**
	 * Returns the internal storyblok type
	 */
	abstract protected function getInternalStoryblokType () : FieldType;
}
