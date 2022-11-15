<?php declare(strict_types=1);

namespace Torr\Storyblok\Structure;

interface DataVisitorInterface
{
	/**
	 * If a data visitor is given, it will be called for every field with the field definition and data.
	 */
	public function onDataVisit (
		FieldDefinitionInterface $field,
		mixed $data,
	) : void;
}
