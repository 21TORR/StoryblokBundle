<?php declare(strict_types=1);

namespace Torr\Storyblok\ComponentFieldDefinition\Field;

use Torr\Storyblok\Api\Manager\ComponentManager;
use Torr\Storyblok\ComponentFieldDefinition\FieldDefinition;
use Torr\Storyblok\ComponentFieldDefinition\FieldType;

final class NumberFieldDefinition extends FieldDefinition
{
	// @inheritDoc
	public static function getType () : FieldType
	{
		return FieldType::Number;
	}


	// @inheritDoc
	public function getSchemaDefinition (ComponentManager $componentManager) : array
	{
		return [];
	}
}
