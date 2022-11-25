<?php declare(strict_types=1);

namespace Torr\Storyblok\Field\Choices;

interface BackedEnumChoiceInterface extends \BackedEnum
{
	/**
	 * Returns the label for the enum
	 */
	public function getLabel () : string;
}
