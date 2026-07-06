<?php declare(strict_types=1);

namespace Torr\Storyblok\Definition\Mapping;

use Torr\Storyblok\Definition\Field\MappedField;
use Torr\Storyblok\Field\FieldType;

/**
 * @final
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
readonly class FloatField extends MappedField
{
	/**
	 */
	public function __construct (
		string $key,
		string $label,
		private int $decimals,
		mixed $defaultValue = null,
		private bool $exportTranslation = false,
		private ?float $minValue = null,
		private ?float $maxValue = null,
		private ?float $steps = null,
	)
	{
		parent::__construct($key, $label, $defaultValue);
	}


	/**
	 *
	 */
	public function getType () : FieldType
	{
		return FieldType::Number;
	}

	/**
	 *
	 */
	public function getManagementApiData () : array
	{
		return array_replace(
			parent::getManagementApiData(),
			[
				"no_translate" => !$this->exportTranslation,
				...array_filter([
					"min_value" => $this->minValue,
					"max_value" => $this->maxValue,
					"decimals" => $this->decimals,
					"steps" => $this->steps,
				], static fn ($value) => null !== $value),
			],
		);
	}

}
