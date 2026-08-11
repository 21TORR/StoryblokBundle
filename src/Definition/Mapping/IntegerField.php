<?php declare(strict_types=1);

namespace Torr\Storyblok\Definition\Mapping;

use Torr\Storyblok\Definition\Field\MappedField;
use Torr\Storyblok\Field\FieldType;

/**
 * @final
 */
// #[\Attribute(\Attribute::TARGET_PROPERTY)]
// readonly class IntegerField extends MappedField
// {
//	/**
//	 */
//	public function __construct (
//		string $label,
//		mixed $defaultValue = null,
//		private bool $exportTranslation = false,
//		private ?int $minValue = null,
//		private ?int $maxValue = null,
//		private ?int $steps = null,
//		?string $key = null,
//	)
//	{
//		parent::__construct($label, $key, $defaultValue);
//	}
//
//
//	/**
//	 *
//	 */
//	public function getType () : FieldType
//	{
//		return FieldType::Number;
//	}
//
//	/**
//	 *
//	 */
//	public function getManagementApiData () : array
//	{
//		return array_replace(
//			parent::getManagementApiData(),
//			[
//				"no_translate" => !$this->exportTranslation,
//				...array_filter([
//					"min_value" => $this->minValue,
//					"max_value" => $this->maxValue,
//					"decimals" => 0,
//					"steps" => $this->steps,
//				], static fn ($value) => null !== $value),
//			],
//		);
//	}
//
// }
//
