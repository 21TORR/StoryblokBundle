<?php declare(strict_types=1);

namespace Torr\Storyblok\Field;

interface NestedFieldDefinitionInterface
{
	/**
	 * Returns the nested fields
	 *
	 * @return FieldDefinitionInterface[]
	 */
	public function getNestedFields () : array;
}
