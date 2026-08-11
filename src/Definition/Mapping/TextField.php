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
		public bool $multiline = false,
		public ?string $regex = null,
		public ?int $maxLength = null,
		public bool $isRightToLeft = false,
		?string $key = null,
		public ?string $defaultValue = null,
	)
	{
		parent::__construct($key, $label);
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
	public function createManagementApiData () : array
	{
		return $this->mergeManagementData([
			"default_value" => $this->defaultValue,
			"max_length" => $this->maxLength,
			"rtl" => $this->isRightToLeft,
			"regex" => $this->regex,
		]);
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
		$context->ensureDataIsValid($contentPath, $storyData, $this, $contentPathHierarchy, [
			// !$this->allowMissingData && $this->required ? new NotNull() : null,
			new Type("string"),
			// We can't validate the length here, as it is not guaranteed if you add
			// the max-length after content was added.
		]);
	}
}
