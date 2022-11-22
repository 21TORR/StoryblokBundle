<?php declare(strict_types=1);

namespace Torr\Storyblok\Structure\Field;

use Symfony\Component\Validator\Constraint;
use Torr\Storyblok\Data\FieldType;
use Torr\Storyblok\Structure\DataVisitorInterface;
use Torr\Storyblok\Structure\FieldDefinitionInterface;
use Torr\Storyblok\Transformer\DataTransformer;
use Torr\Storyblok\Validator\DataValidator;

abstract class AbstractFieldDefinition implements FieldDefinitionInterface
{
	public function __construct (
		public readonly FieldType $storyblokFieldType,
		public readonly ?string $label = null,
		public readonly ?int $position = null,
		public readonly mixed $defaultValue = null,
		public readonly ?string $description = null,
		public readonly bool $translatable = false,
		public readonly bool $required = false,
		public readonly ?string $regexp = null,
		public readonly ?bool $canSync = null,
		public readonly ?bool $isPreviewField = null,
		public readonly array $additionalFieldData = [],
	) {}

	/**
	 * Returns the field definition for usage with the management api.
	 *
	 * @internal
	 */
	public function toManagementApiData () : array
	{
		return [
			"type" => $this->storyblokFieldType->value,
			"display_name" => $this->label,
			"pos" => $this->position,
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
			$this->storyblokFieldType,
			$data,
			$constraints,
		);
	}
}
