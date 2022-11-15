<?php declare(strict_types=1);

namespace Torr\Storyblok\Structure\Field;

use Torr\Storyblok\Data\ComponentGroups;
use Torr\Storyblok\Data\FieldType;
use Torr\Storyblok\Structure\Component\ComponentTypeDefinition;
use Torr\Storyblok\Validator\DataValidator;

final class LinkFieldDefinition extends FieldDefinition
{
	/**
	 * @param array<int, class-string<ComponentTypeDefinition>>|null $componentsAllowList      A list of FQCNs of Components that are allowed to be used with this field.
	 * @param ComponentGroups[]|null                                 $componentGroupsAllowList A list of Component Group Names that are allowed to be used with this field.
	 */
	public function __construct (
		public readonly ?bool $isEmailLink = null,
		public readonly ?bool $isAssetLink = null,
		public readonly ?bool $isInternalLink = null,
		public readonly ?bool $restrictContentTypes = null,
		public readonly ?array $componentsAllowList = null,
		public readonly ?array $componentGroupsAllowList = null,
		public readonly ?string $restrictedFolderPath = null,
		?int $position = null,
		mixed $defaultValue = null,
		?string $label = null,
		?string $description = null,
		bool $translatable = false,
		bool $required = false,
		?string $regexp = null,
		?bool $canSync = null,
		bool $isPreviewField = false,
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
			type: FieldType::Link,
			label: $label,
			position: $position,
			defaultValue: $defaultValue,
			description: $description,
			translatable: $translatable,
			required: $required,
			regexp: $regexp,
			canSync: $canSync,
			isPreviewField: $isPreviewField,
			additionalFieldData: [
				// @todo this config is currently incomplete
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
