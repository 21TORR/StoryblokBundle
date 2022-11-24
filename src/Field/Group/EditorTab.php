<?php declare(strict_types=1);

namespace Torr\Storyblok\Field\Group;

use Torr\Storyblok\Field\FieldType;

final class EditorTab extends AbstractGroupingElement
{
	/**
	 * @inheritDoc
	 */
	protected function getInternalStoryblokType () : FieldType
	{
		return FieldType::Tab;
	}
}
