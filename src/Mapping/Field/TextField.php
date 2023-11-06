<?php declare(strict_types=1);

namespace Torr\Storyblok\Mapping\Field;

use Symfony\Component\Validator\Constraints\Type;
use Torr\Storyblok\Field\FieldType;
use Torr\Storyblok\Hydrator\StoryHydrator;
use Torr\Storyblok\Transformer\DataTransformer;
use Torr\Storyblok\Validator\DataValidator;

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
		private readonly bool $excludeFromTranslationExport = false,
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
	public function transformRawData (array $contentPath, mixed $data, StoryHydrator $hydrator) : ?string
	{
		\assert(null === $data || \is_string($data));

		return DataTransformer::normalizeOptionalString($data);
	}


	/**
	 * @inheritDoc
	 */
	public function validateData (array $contentPath, DataValidator $validator, mixed $data) : void
	{
		$validator->ensureDataIsValid($contentPath, $data, [
			new Type("string"),
		]);
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
				"no_translate" => $this->excludeFromTranslationExport,
			],
		);
	}
}
