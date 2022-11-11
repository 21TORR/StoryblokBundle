<?php declare(strict_types=1);

namespace Torr\Storyblok\ComponentDefinition;

use Torr\Storyblok\Data\StoryblokComponentType;

abstract class UniversalTypeDefinition extends BaseComponentTypeDefinition
{
	// @inheritDoc
	final public static function getComponentType () : StoryblokComponentType
	{
		return StoryblokComponentType::UniversalType;
	}
}
