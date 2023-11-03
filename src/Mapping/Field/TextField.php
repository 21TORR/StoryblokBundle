<?php declare(strict_types=1);

namespace Torr\Storyblok\Mapping\Field;

use Torr\Storyblok\Field\FieldType;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class TextField extends AbstractField
{
	public function __construct (
		string $key,
		string $label,
		?string $defaultValue = null,
		private readonly bool $multiline = false,
		private readonly ?int $maxLength = null,
		private readonly bool $isRightToLeft = false,
	)
	{
		parent::__construct(
			internalStoryblokType: $this->multiline
				? FieldType::TextArea
				: FieldType::Text,
			key: $key,
			label: $label,
			defaultValue: $defaultValue,
		);
	}

	/**
	 * @inheritDoc
	 */
	public function generateManagementApiData () : array
	{
		return \array_replace(
			parent::generateManagementApiData(),
			[
				"rtl" => $this->isRightToLeft,
				"max_length" => $this->maxLength,
			],
		);
	}
}
