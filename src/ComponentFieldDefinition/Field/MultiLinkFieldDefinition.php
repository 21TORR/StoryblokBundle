<?php declare(strict_types=1);

namespace Torr\Storyblok\ComponentFieldDefinition\Field;

use Torr\Storyblok\Api\Manager\ComponentManager;
use Torr\Storyblok\ComponentDefinition\BaseComponentTypeDefinition;
use Torr\Storyblok\ComponentDefinition\ComponentGroups;
use Torr\Storyblok\ComponentFieldDefinition\FieldDefinition;
use Torr\Storyblok\ComponentFieldDefinition\FieldType;

final class MultiLinkFieldDefinition extends FieldDefinition
{
	//region Fields
	/** @var array<int, class-string<BaseComponentTypeDefinition>>|null */
	private readonly ?array $componentsAllowList;
	/** @var ComponentGroups[]|null */
	private readonly ?array $componentGroupsAllowList;
	//endregion

	/**
	 * @param array<int, class-string<BaseComponentTypeDefinition>>|null $componentsAllowList      A list of FQCNs of Components that are allowed to be used with this field.
	 * @param ComponentGroups[]|null                                     $componentGroupsAllowList A list of Component Group Names that are allowed to be used with this field.
	 */
	public function __construct (
		?array $componentsAllowList = null,
		?array $componentGroupsAllowList = null,
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

		$this->componentsAllowList = $componentsAllowList;
		$this->componentGroupsAllowList = $componentGroupsAllowList;
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
	//endregion

	// @inheritDoc
	public static function getType () : FieldType
	{
		return FieldType::MultiLink;
	}


	// @inheritDoc
	public function getSchemaDefinition (ComponentManager $componentManager) : array
	{
		$componentsAllowList = [];
		$componentGroupsAllowList = $componentManager->getOrCreateComponentGroupUuids($this->componentGroupsAllowList);
		/** @var class-string<BaseComponentTypeDefinition>[] $configuredComponentAllowList */
		$configuredComponentAllowList = $this->componentsAllowList ?? [];

		foreach ($configuredComponentAllowList as $allowedComponentTypeDefinition)
		{
			$componentsAllowList[] = $allowedComponentTypeDefinition::getTechnicalName();
		}

		return [
			"component_whitelist" => $componentsAllowList,
			"component_group_whitelist" => $componentGroupsAllowList,
		];
	}
}
