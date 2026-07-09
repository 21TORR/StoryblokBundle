<?php declare(strict_types=1);

namespace Torr\Storyblok\Management;

use Torr\Storyblok\Definition\Data\ComponentDefinition;
use Torr\Storyblok\Definition\DefinitionRegistry;
use Torr\Storyblok\Management\Generator\FieldSchemaCollection;

/**
 * @final
 */
readonly class ManagementApiGenerator
{
	/**
	 */
	public function __construct (
		private DefinitionRegistry $registry,
	) {}

	/**
	 *
	 */
	public function generateManagementApiPayload (ComponentDefinition $component) : array
	{
		$schemaCollection = new FieldSchemaCollection($this->registry);

		foreach ($component->fields as $field)
		{
			$schemaCollection->add($field);
		}

		return [
			"name" => $component->key,
			"display_name" => $component->label,
			"description" => $component->description,
			...$component->type->toManagementApiData(),
			"schema" => $schemaCollection->schemas,
		];
	}
}
