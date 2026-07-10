<?php declare(strict_types=1);

namespace Torr\Storyblok\Management;

use Storyblok\ManagementApi\Data\Component;
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
	public function generateManagementApiPayload (ComponentDefinition $component) : Component
	{
		$apiComponent = $component->type->toManagementApiComponent()
			->setName($component->key)
			->setDisplayName($component->label)
			->set("description", $component->description);

		$schemaCollection = new FieldSchemaCollection($this->registry, $apiComponent);

		foreach ($component->fields as $field)
		{
			$schemaCollection->add($field);
		}

		return $apiComponent;
	}
}
