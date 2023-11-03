<?php declare(strict_types=1);

namespace Torr\Storyblok\Visitor;

use Torr\Storyblok\Field\FieldDefinition;

interface DataVisitorInterface
{
	/**
	 * If a data visitor is given, it will be called for every field with the field definition and data.
	 */
	public function onDataVisit (
		FieldDefinition $field,
		mixed $data,
	) : void;
}
