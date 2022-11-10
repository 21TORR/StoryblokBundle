<?php declare(strict_types=1);

namespace Torr\Storyblok\ComponentFieldDefinition\Field;

use Torr\Storyblok\Api\Manager\ComponentManager;
use Torr\Storyblok\ComponentDefinition\BaseComponentTypeDefinition;
use Torr\Storyblok\ComponentDefinition\ComponentGroups;
use Torr\Storyblok\ComponentFieldDefinition\FieldDefinition;
use Torr\Storyblok\ComponentFieldDefinition\FieldType;
use Torr\Storyblok\Data\StoryblokRichTextToolbarOption;

final class RichTextFieldDefinition extends FieldDefinition
{
	//region Fields
	private readonly ?int $maxLength;
	/** @var array<int, class-string<BaseComponentTypeDefinition>>|null */
	private readonly ?array $componentsAllowList;
	/** @var ComponentGroups[]|null */
	private readonly ?array $componentGroupsAllowList;
	private readonly ?array $toolbarOptions;
	private readonly ?array $styleOptions;
	//endregion

	/**
	 * @param array<int, class-string<BaseComponentTypeDefinition>>|null $componentsAllowList      A list of FQCNs of Components that are allowed to be used with this field.
	 * @param ComponentGroups[]|null                                     $componentGroupsAllowList A list of Component Group Names that are allowed to be used with this field.
	 * @param StoryblokRichTextToolbarOption[]|null                      $toolbarOptions
	 * @param array<array{name: string, value: string}>|null             $styleOptions
	 */
	public function __construct (
		?int $maxLength = null,
		?array $componentsAllowList = null,
		?array $componentGroupsAllowList = null,
		?array $toolbarOptions = null,
		?array $styleOptions = null,
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
		parent::__construct(
			label: $label,
			position: $position,
			defaultValue: $defaultValue,
			description: $description,
			translatable: $translatable,
			required: $required,
			regexp: $regexp,
			canSync: $canSync,
			isPreviewField: $isPreviewField,
		);

		$this->maxLength = $maxLength;
		$this->componentsAllowList = $componentsAllowList;
		$this->componentGroupsAllowList = $componentGroupsAllowList;
		$this->toolbarOptions = $toolbarOptions;
		$this->styleOptions = $styleOptions;
	}

	//region Field Accessors
	/**
	 * @return array<int, class-string<BaseComponentTypeDefinition>>|null
	 */
	public function getComponentsAllowList () : ?array
	{
		return $this->componentsAllowList;
	}

	/**
	 * @return ComponentGroups[]|null
	 */
	public function getComponentGroupsAllowList () : ?array
	{
		return $this->componentGroupsAllowList;
	}

	public function getToolbarOptions () : ?array
	{
		return $this->toolbarOptions;
	}

	public function getStyleOptions () : ?array
	{
		return $this->styleOptions;
	}
	//endregion

	public static function getType () : FieldType
	{
		return FieldType::RichText;
	}


	// @inheritDoc
	public function getSchemaDefinition (ComponentManager $componentManager) : array
	{
		$componentsAllowList = [];
		$componentGroupsAllowList = $componentManager->getOrCreateComponentGroupUuids($this->componentGroupsAllowList);
		$toolbar = [];

		/** @var class-string<BaseComponentTypeDefinition>[] $configuredComponentAllowList */
		$configuredComponentAllowList = $this->componentsAllowList ?? [];

		foreach ($configuredComponentAllowList as $allowedComponentTypeDefinition)
		{
			$componentsAllowList[] = $allowedComponentTypeDefinition::getTechnicalName();
		}

		foreach ($this->toolbarOptions ?? [] as $toolbarOption)
		{
			$toolbar[] = $toolbarOption->value;
		}

		$hasComponentsRestrictions = 0 < \count($componentsAllowList);
		$hasComponentGroupRestrictions = 0 < \count($componentGroupsAllowList);

		return [
			"max_length" => $this->maxLength,
			"customize_toolbar" => 0 < \count($toolbar),
			"toolbar" => $toolbar,
			"restrict_type" => match ([$hasComponentsRestrictions, $hasComponentGroupRestrictions])
			{
				[true, false] => "",
				[false, true] => "groups",
				default => null,
			},
			"component_whitelist" => $componentsAllowList,
			"component_group_whitelist" => $componentGroupsAllowList,
		];
	}
}
