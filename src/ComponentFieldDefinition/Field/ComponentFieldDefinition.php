<?php declare(strict_types=1);

namespace Torr\Storyblok\ComponentFieldDefinition\Field;

use Torr\Storyblok\Api\Manager\ComponentManager;
use Torr\Storyblok\ComponentDefinition\BaseComponentTypeDefinition;
use Torr\Storyblok\ComponentDefinition\ComponentGroups;
use Torr\Storyblok\ComponentFieldDefinition\FieldDefinition;
use Torr\Storyblok\ComponentFieldDefinition\FieldType;

final class ComponentFieldDefinition extends FieldDefinition
{
	//region Fields
	private readonly ?int $maxNumberOfComponents;
	private readonly ?bool $restrictContentTypes;
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
		?bool $restrictContentTypes = null,
		?array $componentsAllowList = null,
		?array $componentGroupsAllowList = null,
		?int $maxNumberOfComponents = null,
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
		parent::__construct(
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
		);

		$this->maxNumberOfComponents = $maxNumberOfComponents;
		$this->restrictContentTypes = $restrictContentTypes;
		$this->componentsAllowList = $componentsAllowList;
		$this->componentGroupsAllowList = $componentGroupsAllowList;
	}

	//region Field Accessors
	public function getMaxNumberOfComponents () : ?int
	{
		return $this->maxNumberOfComponents;
	}

	public function getRestrictContentTypes () : ?bool
	{
		return $this->restrictContentTypes;
	}

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
		return FieldType::Bloks;
	}


	// @inheritDoc
	public function getSchemaDefinition (ComponentManager $componentManager) : array
	{
		$componentGroupsAllowList = $componentManager->getOrCreateComponentGroupUuids($this->componentGroupsAllowList);
		$componentAllowList = [];
		/** @var array<int, class-string<BaseComponentTypeDefinition>> $configuredComponentAllowList */
		$configuredComponentAllowList = $this->componentsAllowList ?? [];

		foreach ($configuredComponentAllowList as $allowedComponentTypeDefinition)
		{
			$componentAllowList[] = $allowedComponentTypeDefinition::getTechnicalName();
		}

		return [
			"maximum" => $this->maxNumberOfComponents,
			"restrict_components" => 0 < \count($componentAllowList) || 0 < \count($componentGroupsAllowList),
			"restrict_content_types" => $this->restrictContentTypes,
			"component_whitelist" => $componentAllowList,
			"component_group_whitelist" => $componentGroupsAllowList,
		];
	}
}
