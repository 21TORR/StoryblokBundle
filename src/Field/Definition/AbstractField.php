<?php declare(strict_types=1);

namespace Torr\Storyblok\Field\Definition;

use Torr\Storyblok\Context\ComponentContext;
use Torr\Storyblok\Field\FieldDefinitionInterface;
use Torr\Storyblok\Field\FieldType;
use Torr\Storyblok\Visitor\DataVisitorInterface;

/**
 * Base class for any field that is used in the app
 *
 * @internal
 */
abstract class AbstractField implements FieldDefinitionInterface
{
	private ?bool $canSync = false;
	private ?bool $isPreviewField = false;
	private bool $required = false;
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
	 * @return $this
	 */
	public function enablePreview (
		?bool $canSync = null,
	) : static
	{
		$this->isPreviewField = true;
		$this->canSync = $canSync;

		return $this;
	}

	/**
	 * Returns the internal storyblok type
	 */
	abstract protected function getInternalStoryblokType () : FieldType;

	/**
	 * Returns the field definition for usage with the management api.
	 *
	 * @internal
	 */
	public function toManagementApiData (
		int $position,
	) : array
	{
		return [
			"type" => $this->getInternalStoryblokType()->value,
			"display_name" => $this->label,
			"pos" => $position,
			"default_value" => $this->defaultValue,
			"description" => $this->description,
			"tooltip" => $this->descriptionAsTooltip,
			"translatable" => $this->translatable,
			"required" => $this->required,
			"regexp" => $this->regexp,
			"can_sync" => $this->canSync,
			"preview_field" => $this->isPreviewField,
		];
	}

	/**
	 * @template T
	 *
	 * @param T $data
	 *
	 * @return T
	 */
	public function transformData (
		mixed $data,
		ComponentContext $context,
		?DataVisitorInterface $dataVisitor = null,
	) : mixed
	{
		$dataVisitor?->onDataVisit($this, $data);

		return $data;
	}
}
