<?php declare(strict_types=1);

namespace Torr\Storyblok\Definition\Mapping;

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
		string $key,
		string $label,
		?string $defaultValue = null,
		private bool $multiline = false,
		private ?int $maxLength = null,
		private bool $isRightToLeft = false,
		private bool $exportTranslation = true,
	)
	{
		parent::__construct($key, $label, $defaultValue);

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
	public function getManagementApiData () : array
	{
		return array_replace(
			parent::getManagementApiData(),
			[
				"rtl" => $this->isRightToLeft,
				"max_length" => $this->maxLength,
				"no_translate" => !$this->exportTranslation,
			],
		);
	}
}
