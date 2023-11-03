<?php declare(strict_types=1);

namespace Torr\Storyblok\Field\Definition;

use Torr\Storyblok\Field\FieldDefinition;
use Torr\Storyblok\Field\FieldType;
use Torr\Storyblok\Management\ManagementApiData;

/**
 * Base class for any field that is used in the app
 *
 * @internal
 */
abstract class AbstractField implements FieldDefinition
{
	private ?bool $canSync = false;
	private ?bool $useAsAdminDisplayName = false;
	protected bool $required = false;
	private ?string $regexp = null;
	private bool $translatable = false;
	private ?string $description = null;
	private bool $descriptionAsTooltip = false;
	protected bool $allowMissingData = false;

	public function __construct (
		private readonly string $label,
		private readonly mixed $defaultValue = null,
	) {}

	/**
	 * Add a description to this field
	 *
	 * @return $this
	 */
	public function addDescription (
		string $description,
		bool $showAsTooltip = false,
	) : static
	{
		$this->description = $description;
		$this->descriptionAsTooltip = $showAsTooltip;

		return $this;
	}

	/**
	 * Makes this field translatable
	 *
	 * @return $this
	 */
	public function enableTranslations () : static
	{
		$this->translatable = true;

		return $this;
	}

	/**
	 * Enables validation for this field
	 *
	 * @param bool $allowMissingData This parameter should only be set to `true` when the given field has been added to
	 *                               a component after it has already been published and used. Storyblok does not send
	 *                               default values on existing Stories when a new field has been added.
	 *
	 * @return $this
	 */
	public function enableValidation (
		bool $required = true,
		?string $regexp = null,
		bool $allowMissingData = false,
	) : static
	{
		$this->required = $required;
		$this->regexp = $regexp;
		$this->allowMissingData = $allowMissingData;

		return $this;
	}

	/**
	 * Enables the preview for this field
	 *
	 * @deprecated use {@link self::useAsAdminDisplayName()} instead
	 *
	 * @return $this
	 */
	public function enablePreview (
		?bool $canSync = null,
	) : static
	{
		return $this->useAsAdminDisplayName($canSync);
	}

	/**
	 * Uses this field as admin display preview value
	 *
	 * @return $this
	 */
	public function useAsAdminDisplayName (
		?bool $canSync = null,
	) : static
	{
		$this->useAsAdminDisplayName = true;
		$this->canSync = $canSync;

		return $this;
	}

	/**
	 * Returns the internal storyblok type
	 */
	abstract protected function getInternalStoryblokType () : FieldType;

	/**
	 * @inheritDoc
	 */
	public function registerManagementApiData (string $key, ManagementApiData $managementApiData) : void
	{
		$managementApiData->registerField($key, $this->toManagementApiData());
	}

	/**
	 * Returns the field definition for usage with the management api.
	 */
	protected function toManagementApiData () : array
	{
		return [
			"type" => $this->getInternalStoryblokType()->value,
			"display_name" => $this->label,
			"default_value" => $this->defaultValue,
			"description" => $this->description,
			"tooltip" => $this->descriptionAsTooltip,
			"translatable" => $this->translatable,
			"required" => $this->required,
			"regex" => $this->regexp,
			"can_sync" => $this->canSync,
			"preview_field" => $this->useAsAdminDisplayName,
		];
	}

	/**
	 * This method returns whether the field is used as the storyblok default value.
	 * It is deliberately name differently, so that it doesn't get listed in autocompletion.
	 *
	 * @internal
	 *
	 * @private
	 */
	public function isStoryblokPreviewField () : ?bool
	{
		return $this->useAsAdminDisplayName;
	}
}
