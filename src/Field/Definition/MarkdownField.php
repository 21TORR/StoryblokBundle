<?php declare(strict_types=1);

namespace Torr\Storyblok\Field\Definition;

use Symfony\Component\Validator\Constraints\Type;
use Torr\Storyblok\Context\StoryblokContext;
use Torr\Storyblok\Field\FieldType;
use Torr\Storyblok\Validator\DataValidator;
use Torr\Storyblok\Visitor\DataVisitorInterface;

final class MarkdownField extends AbstractField
{
	/**
	 * @inheritDoc
	 */
	public function __construct (
		string $label,
		mixed $defaultValue = null,
		private readonly bool $hasRichMarkdown = true,
		private readonly ?int $maxLength = null,
		private readonly bool $isRightToLeft = false,
	)
	{
		parent::__construct($label, $defaultValue);
	}

	/**
	 * @inheritDoc
	 */
	protected function getInternalStoryblokType () : FieldType
	{
		return FieldType::Markdown;
	}

	/**
	 * @inheritDoc
	 */
	public function toManagementApiData (int $position) : array
	{
		return \array_replace(
			parent::toManagementApiData($position),
			[
				"rich_markdown" => $this->hasRichMarkdown,
				"rtl" => $this->isRightToLeft,
				"max_length" => $this->maxLength,
			],
		);
	}

	/**
	 * @inheritDoc
	 */
	public function validateData (DataValidator $validator, array $contentPath, mixed $data) : void
	{
		$validator->ensureDataIsValid(
			$contentPath,
			$this,
			$data,
			[
				new Type("string"),
			],
		);
	}

	/**
	 * @inheritDoc
	 */
	public function transformData (
		mixed $data,
		StoryblokContext $dataContext,
		?DataVisitorInterface $dataVisitor = null,
	) : ?string
	{
		\assert(null === $data || \is_string($data));

		$transformed = $dataContext->dataTransformer->normalizeOptionalString($data);

		return parent::transformData($transformed, $dataContext, $dataVisitor);
	}

}
