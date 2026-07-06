<?php declare(strict_types=1);

namespace Torr\Storyblok\Definition\Field;

use Torr\Storyblok\Context\ComponentContext;
use Torr\Storyblok\Field\FieldType;

abstract readonly class MappedField
{
	/**
	 */
	public function __construct (
		public string $key,
		public string $label,
		public mixed $defaultValue = null,
	) {}

	/**
	 * Returns the Storyblok type
	 */
	abstract public function getType () : FieldType;

	/**
	 *
	 */
	public function getManagementApiData () : array
	{
		return [
			"type" => $this->getType()->value,
			"display_name" => $this->label,
			"default_value" => $this->defaultValue,
		];
	}

	/**
	 */
	public function transformStoryblokValue (
		mixed $value,
		ComponentContext $context,
	) : mixed
	{
		return $value;
	}
}
