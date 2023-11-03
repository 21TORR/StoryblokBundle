<?php declare(strict_types=1);

namespace Torr\Storyblok\Field;

interface NestedFieldDefinitionInterface
{
	/**
	 * Returns the nested fields
	 *
	 * @return FieldDefinition[]
	 */
	public function getNestedFields () : array;
}
