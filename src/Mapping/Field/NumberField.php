<?php declare(strict_types=1);

namespace Torr\Storyblok\Mapping\Field;

use Torr\Storyblok\Exception\Mapping\InvalidFieldDefinitionException;
use Torr\Storyblok\Field\FieldType;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class NumberField extends AbstractField
{
	/**
	 */
	public function __construct (string $key, string $label, mixed $defaultValue = null)
	{
		parent::__construct(
			FieldType::Number,
			$key,
			$label,
			$defaultValue,
		);
	}
}
