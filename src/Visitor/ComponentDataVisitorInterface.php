<?php declare(strict_types=1);

namespace Torr\Storyblok\Visitor;

use Torr\Storyblok\Component\AbstractComponent;
use Torr\Storyblok\Field\FieldDefinitionInterface;

interface ComponentDataVisitorInterface extends DataVisitorInterface
{
	/**
	 * If a data visitor is given, it will be called for every field with the field definition or component and data.
	 */
	public function onDataVisit (
		FieldDefinitionInterface|AbstractComponent $field,
		mixed $data,
	) : void;
}
