<?php declare(strict_types=1);

namespace Torr\Storyblok\Definition\Mapping;

use Symfony\Component\Validator\Constraints\Type;
use Torr\Storyblok\Context\ComponentContext;
use Torr\Storyblok\Definition\Data\FieldDefinition;
use Torr\Storyblok\Definition\Field\MappedField;
use Torr\Storyblok\Field\FieldType;

/**
 * @final
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
readonly class TextField extends MappedField
{
	/**
	 */
	public function __construct (
		string $label,
		private bool $multiline = false,
		private ?int $maxLength = null,
		private bool $isRightToLeft = false,
		private bool $exportTranslation = true,
		?string $key = null,
		?string $defaultValue = null,
	)
	{
		parent::__construct($label, $key, $defaultValue);
	}

	/**
	 *
	 */
	#[\Override]
	public function getType () : FieldType
	{
		return $this->multiline
			? FieldType::TextArea
			: FieldType::Text;
	}

	/**
	 *
	 */
	#[\Override]
	public function toManagementApiData () : array
	{
		return array_replace(
			parent::toManagementApiData(),
			[
				"rtl" => $this->isRightToLeft,
				"max_length" => $this->maxLength,
				"no_translate" => !$this->exportTranslation,
			],
		);
	}

	/**
	 *
	 */
	#[\Override]
	public function validateValue (
		string $contentPath,
		array $storyData,
		FieldDefinition $fieldDefinition,
		ComponentContext $context,
		array $contentPathHierarchy,
	) : void
	{
		$value = $storyData[$contentPath] ?? null;

		$context->ensureDataIsValid($contentPath, $storyData, $this, $contentPathHierarchy, [
			// !$this->allowMissingData && $this->required ? new NotNull() : null,
			new Type("string"),
			// We can't validate the length here, as it is not guaranteed if you add
			// the max-length after content was added.
		]);
	}
}
