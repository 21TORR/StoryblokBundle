<?php declare(strict_types=1);

namespace Torr\Storyblok\ComponentFieldDefinition\Field;

use Torr\Storyblok\ComponentFieldDefinition\FieldType;

final class MultiOptionsFieldDefinition extends OptionFieldDefinition
{
	// @inheritDoc
	public static function getType () : FieldType
	{
		return FieldType::Options;
	}
}
