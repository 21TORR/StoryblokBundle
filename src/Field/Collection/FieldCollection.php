<?php declare(strict_types=1);

namespace Torr\Storyblok\Field\Collection;

use Torr\Storyblok\Exception\Story\UnknownFieldException;
use Torr\Storyblok\Field\FieldDefinition;
use Torr\Storyblok\Field\Helper\FieldDefinitionHelper;
use Torr\Storyblok\Field\NestedFieldDefinitionInterface;

final class FieldCollection
{
	/** @var array<string, FieldDefinition> */
	private array $allFields = [];

	public function __construct (
		/** @var array<string, FieldDefinition> $rootFields */
		private readonly array $rootFields,
	)
	{
		$this->indexFields($this->rootFields);
		FieldDefinitionHelper::ensureMaximumOneAdminDisplayName($this->rootFields);
	}

	/**
	 * @param array<string, FieldDefinition> $fields
	 */
	private function indexFields (array $fields) : void
	{
		foreach ($fields as $fieldName => $field)
		{
			$this->allFields[$fieldName] = $field;

			if ($field instanceof NestedFieldDefinitionInterface)
			{
				$this->indexFields($field->getNestedFields());
			}
		}
	}


	/**
	 * @return array<string, FieldDefinition>
	 */
	public function getRootFields () : array
	{
		return $this->rootFields;
	}

	/**
	 * Returns a single transformable field
	 */
	public function getField (string $key) : FieldDefinition
	{
		$field = $this->allFields[$key] ?? null;

		if (null === $field)
		{
			throw new UnknownFieldException(\sprintf(
				"Unknown field %s",
				$key,
			));
		}

		return $field;
	}
}
