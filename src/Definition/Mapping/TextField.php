<?php declare(strict_types=1);

namespace Torr\Storyblok\Definition\Mapping;

use Storyblok\ManagementApi\Data\Fields\Schema\FieldGeneric;
use Storyblok\ManagementApi\Data\Fields\Schema\FieldText;
use Storyblok\ManagementApi\Data\Fields\Schema\FieldTextarea;
use Symfony\Component\Validator\Constraints\Type;
use Torr\Storyblok\Context\ComponentContext;
use Torr\Storyblok\Definition\Data\FieldDefinition;
use Torr\Storyblok\Definition\Field\MappedField;

/**
 * @final
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
readonly class TextField extends MappedField
{
	/**
	 */
	public function __construct (
		public string $label,
		public bool $multiline = false,
		public ?string $regex = null,
		public ?int $maxLength = null,
		public bool $isRightToLeft = false,
		public bool $exportTranslation = true,
		?string $key = null,
		public ?string $defaultValue = null,
		public bool $translatable = true,
	)
	{
		parent::__construct($key);
	}

	/**
	 *
	 */
	#[\Override]
	public function createApiData (string $key) : FieldGeneric
	{
		$field = $this->multiline
			? new FieldTextarea($key)
			: new FieldText($key);

		if (null !== $this->defaultValue)
		{
			$field->setDefaultValue($this->defaultValue);
		}

		if (null !== $this->regex)
		{
			$field->setRegex($this->regex);
		}

		return $field
			->setDisplayName($this->label)
			->set("max_length", $this->maxLength)
			->set("rtl", $this->isRightToLeft)
			->setNoTranslate(!$this->translatable);
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
