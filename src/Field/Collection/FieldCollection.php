<?php declare(strict_types=1);

namespace Torr\Storyblok\Field\Collection;

use Torr\Storyblok\Field\FieldDefinitionInterface;
use Torr\Storyblok\Field\NestedFieldDefinitionInterface;

final class FieldCollection
{
	/** @var array<string, FieldDefinitionInterface> */
	private array $transformableFields = [];

	public function __construct (
		/** @var array<string, FieldDefinitionInterface> $rootFields */
		private readonly array $rootFields,
	)
	{
		$this->indexFields($this->rootFields);
	}

	/**
	 * @param array<string, FieldDefinitionInterface> $fields
	 */
	private function indexFields (array $fields) : void
	{
		foreach ($fields as $fieldName => $field)
		{
			if ($field instanceof NestedFieldDefinitionInterface)
			{
				$this->indexFields($field->getNestedFields());
			}
			else
			{
				$this->transformableFields[$fieldName] = $field;
			}
		}
	}

	/**
	 * @return array<string, FieldDefinitionInterface>
	 */
	public function getTransformableFields () : array
	{
		return $this->transformableFields;
	}

	/**
	 * @return array<string, FieldDefinitionInterface>
	 */
	public function getRootFields () : array
	{
		return $this->rootFields;
	}
}
