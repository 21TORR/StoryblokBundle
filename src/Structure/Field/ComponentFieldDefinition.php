<?php declare(strict_types=1);

namespace Torr\Storyblok\Structure\Field;

use Torr\Storyblok\Data\ComponentGroups;
use Torr\Storyblok\Data\FieldType;
use Torr\Storyblok\Structure\Component\ComponentTypeDefinition;
use Torr\Storyblok\Validator\DataValidator;

final class ComponentFieldDefinition extends FieldDefinition
{
	/**
	 * @param array<int, class-string<ComponentTypeDefinition>>|null $componentsAllowList      A list of FQCNs of Components that are allowed to be used with this field.
	 * @param ComponentGroups[]|null                                 $componentGroupsAllowList A list of Component Group Names that are allowed to be used with this field.
	 */
	public function __construct (
		public readonly ?bool $restrictContentTypes = null,
		public readonly ?array $componentsAllowList = null,
		public readonly ?array $componentGroupsAllowList = null,
		public readonly ?int $maxNumberOfComponents = null,
		?int $position = null,
		mixed $defaultValue = null,
		?string $label = null,
		?string $description = null,
		bool $translatable = false,
		bool $required = false,
		?string $regexp = null,
		?bool $canSync = null,
	)
	{
		$serializedComponentsAllowList = [];
		// @todo think about how we'll replace this kind of necessary logic in the future
		//$serializedComponentGroupsAllowList = $componentManager->getOrCreateComponentGroupUuids($this->componentGroupsAllowList);
		$serializedComponentGroupsAllowList = [];

		foreach (($componentsAllowList ?? []) as $allowedComponentTypeDefinition)
		{
			$serializedComponentsAllowList[] = $allowedComponentTypeDefinition::getTechnicalName();
		}

		parent::__construct(
			type: FieldType::Bloks,
			label: $label,
			position: $position,
			defaultValue: $defaultValue,
			description: $description,
			translatable: $translatable,
			required: $required,
			regexp: $regexp,
			canSync: $canSync,
			// A component field can't be a preview field
			isPreviewField: false,
			additionalFieldData: [
				"maximum" => $this->maxNumberOfComponents,
				"restrict_components" => 0 < \count($serializedComponentsAllowList) || 0 < \count($serializedComponentGroupsAllowList),
				"restrict_content_types" => $this->restrictContentTypes,
				"component_whitelist" => $serializedComponentsAllowList,
				"component_group_whitelist" => $serializedComponentGroupsAllowList,
			],
		);
	}

	/**
	 * @inheritDoc
	 */
	public function validateData (DataValidator $validator, array $path, mixed $data, ) : void
	{
		// @todo add implementation
	}
}
