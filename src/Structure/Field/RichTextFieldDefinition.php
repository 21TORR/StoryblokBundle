<?php declare(strict_types=1);

namespace Torr\Storyblok\Structure\Field;

use Torr\Storyblok\Data\ComponentGroups;
use Torr\Storyblok\Data\FieldType;
use Torr\Storyblok\Data\RichTextToolbarOption;
use Torr\Storyblok\Structure\Component\ComponentTypeDefinition;
use Torr\Storyblok\Validator\DataValidator;

final class RichTextFieldDefinition extends FieldDefinition
{
	/**
	 * @param array<int, class-string<ComponentTypeDefinition>>|null $componentsAllowList      A list of FQCNs of Components that are allowed to be used with this field.
	 * @param ComponentGroups[]|null                                 $componentGroupsAllowList A list of Component Group Names that are allowed to be used with this field.
	 * @param RichTextToolbarOption[]|null                           $toolbarOptions
	 * @param array<array{name: string, value: string}>|null         $styleOptions
	 */
	public function __construct (
		public readonly ?int $maxLength = null,
		public readonly ?array $componentsAllowList = null,
		public readonly ?array $componentGroupsAllowList = null,
		public readonly ?array $toolbarOptions = null,
		public readonly ?array $styleOptions = null,
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
		$toolbar = [];

		foreach (($componentsAllowList ?? []) as $allowedComponentTypeDefinition)
		{
			$serializedComponentsAllowList[] = $allowedComponentTypeDefinition::getTechnicalName();
		}

		foreach (($toolbarOptions ?? []) as $toolbarOption)
		{
			$toolbar[] = $toolbarOption->value;
		}

		$hasComponentsRestrictions = 0 < \count($serializedComponentsAllowList);
		$hasComponentGroupRestrictions = 0 < \count($serializedComponentGroupsAllowList);

		parent::__construct(
			type: FieldType::RichText,
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
				"max_length" => $maxLength,
				"customize_toolbar" => 0 < \count($toolbar),
				"toolbar" => $toolbar,
				"restrict_type" => match ([$hasComponentsRestrictions, $hasComponentGroupRestrictions])
				{
					[true, false] => "",
					[false, true] => "groups",
					default => null,
				},
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
