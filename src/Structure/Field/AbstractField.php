<?php declare(strict_types=1);

namespace Torr\Storyblok\Structure\Field;

use Symfony\Component\Validator\Constraint;
use Torr\Storyblok\Data\FieldType;
use Torr\Storyblok\Structure\DataVisitorInterface;
use Torr\Storyblok\Structure\FieldDefinitionInterface;
use Torr\Storyblok\Transformer\DataTransformer;
use Torr\Storyblok\Validator\DataValidator;

/**
 * Base class for any field that is used in the app
 *
 * @internal
 */
abstract class AbstractField implements FieldDefinitionInterface
{
	protected ?bool $canSync = false;
	protected ?bool $isPreviewField = false;
	protected bool $required = false;
	protected ?string $regexp = null;
	protected bool $translatable = false;

	public function __construct (
		protected readonly string $label,
		protected readonly mixed $defaultValue = null,
		protected readonly ?string $description = null,
		protected readonly array $additionalFieldData = [],
	) {}

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
	 * @return $this
	 */
	public function enableValidation (
		bool $required = false,
		?string $regexp = null,
	) : static
	{
		$this->required = $required;
		$this->regexp = $regexp;

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
			"translatable" => $this->translatable,
			"required" => $this->required,
			"regexp" => $this->regexp,
			"can_sync" => $this->canSync,
			"preview_field" => $this->isPreviewField,
			...$this->additionalFieldData,
		];
	}

	/**
	 * @template T
	 *
	 * @param T $data
	 *
	 * @return T
	 */
	public function transformValue (
		mixed $data,
		DataTransformer $dataTransformer,
		?DataVisitorInterface $dataVisitor = null,
	) : mixed
	{
		$dataVisitor?->onDataVisit($this, $data);

		return $data;
	}

	/**
	 * Ensures that the value is valid
	 *
	 * @param array<Constraint|null> $constraints
	 */
	protected function ensureDataIsValid (
		DataValidator $validator,
		array $path,
		mixed $data,
		array $constraints,
	) : void
	{
		$validator->ensureDataIsValid(
			$path,
			$this,
			$data,
			$constraints,
		);
	}
}
