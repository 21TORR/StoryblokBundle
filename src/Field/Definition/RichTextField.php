<?php declare(strict_types=1);

namespace Torr\Storyblok\Field\Definition;

use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Type;
use Torr\Storyblok\Component\Filter\ComponentFilter;
use Torr\Storyblok\Context\ComponentContext;
use Torr\Storyblok\Field\FieldType;
use Torr\Storyblok\Field\RichText\RichTextStyling;
use Torr\Storyblok\Manager\Sync\Filter\ResolvableComponentFilter;
use Torr\Storyblok\RichText\HtmlToRichTextTransformer;
use Torr\Storyblok\RichText\LinkMarksRichTextTransformer;
use Torr\Storyblok\Visitor\DataVisitorInterface;

final class RichTextField extends AbstractField
{
	/**
	 * @inheritDoc
	 *
	 * The style options have the format
	 *   ui label => css class name
	 *
	 * @param list<RichTextStyling> $toolbarOptions
	 * @param array<string, string> $styleOptions
	 */
	public function __construct (
		string $label,
		mixed $defaultValue = null,
		private readonly ?int $maxLength = null,
		private readonly ComponentFilter $allowedComponents = new ComponentFilter(),
		private readonly array $toolbarOptions = [],
		private readonly array $styleOptions = [],
		/**
		 * Allows migration text content from text fields to a rich text field.
		 */
		private readonly bool $automaticallyTransformNonRichTextContent = false,
	)
	{
		parent::__construct($label, $defaultValue);
	}

	/**
	 * @inheritDoc
	 */
	protected function getInternalStoryblokType () : FieldType
	{
		return FieldType::RichText;
	}

	/**
	 * @inheritDoc
	 */
	protected function toManagementApiData () : array
	{
		$formattedStyleOptions = [];

		foreach ($this->styleOptions as $name => $value)
		{
			$formattedStyleOptions[] = [
				"name" => $name,
				"value" => $value,
			];
		}

		return array_replace(
			parent::toManagementApiData(),
			[
				"max_length" => $this->maxLength,
				"customize_toolbar" => !empty($this->toolbarOptions),
				"toolbar" => array_map(
					static fn (RichTextStyling $option) => $option->value,
					$this->toolbarOptions,
				),
				"component_whitelist" => new ResolvableComponentFilter(
					$this->allowedComponents,
					"component_whitelist",
					"restrict_components",
				),
				"style_options" => $formattedStyleOptions,
				// can't set the `no_translate` field, as it is always enabled
			],
		);
	}

	/**
	 * @inheritDoc
	 */
	public function validateData (ComponentContext $context, array $contentPath, mixed $data, array $fullData) : void
	{
		$context->ensureDataIsValid(
			$contentPath,
			$this,
			$data,
			[
				!$this->allowMissingData && $this->required ? new NotNull() : null,
				new Type(
					$this->automaticallyTransformNonRichTextContent
						? ["array", "string"]
						: "array",
				),
			],
		);
	}

	/**
	 * @inheritDoc
	 */
	public function transformData (
		mixed $data,
		ComponentContext $context,
		array $fullData,
		?DataVisitorInterface $dataVisitor = null,
	) : ?array
	{
		\assert(null === $data || \is_array($data) || \is_string($data));

		$fieldData = $this->automaticallyTransformNonRichTextContent && \is_string($data)
			? $this->transformDataToRichTextArray($data)
			: $data;

		\assert(null === $fieldData || \is_array($fieldData));

		$contentTransformer = new LinkMarksRichTextTransformer();

		$transformed = null !== $fieldData && !$this->contentIsEmpty($fieldData)
			? $contentTransformer->transform($fieldData)
			: null;

		$content = $transformed["content"] ?? null;

		if (\is_array($content))
		{
			$transformed["_bloks"] = $this->fetchBlockDataMap(
				$content,
				$context,
				$dataVisitor,
			);
		}

		$dataVisitor?->onDataVisit($this, $transformed);

		return $transformed;
	}

	/**
	 * Transforms data of all embedded bloks
	 */
	private function fetchBlockDataMap (
		array $content,
		ComponentContext $context,
		?DataVisitorInterface $dataVisitor = null,
	) : array
	{
		$map = [];

		foreach ($content as $section)
		{
			if ("blok" !== $section["type"])
			{
				continue;
			}

			$items = $section["attrs"]["body"] ?? [];

			foreach ($items as $blok)
			{
				$component = $context->getComponentByKey($blok["component"]);
				$map[$blok["_uid"]] = $component->transformData($blok, $context, $dataVisitor);
			}
		}

		return $map;
	}

	/**
	 * Checks whether the given content is empty
	 */
	private function contentIsEmpty (array $data) : bool
	{
		$paragraphs = $data["content"];

		if (\count($paragraphs) > 1)
		{
			return false;
		}

		$firstItem = $data["content"][0] ?? [];
		$firstItemType = $firstItem["type"] ?? null;
		$firstItemContent = $firstItem["content"] ?? [];

		return 0 === \count($firstItemContent)
			&& \in_array($firstItemType, ["paragraph", "heading"], true);
	}

	/**
	 * Transforms a string to rich text data
	 */
	private function transformDataToRichTextArray (string $data) : ?array
	{
		return (new HtmlToRichTextTransformer())
			->parseHtmlToRichText(
				\sprintf("<p>%s</p>", $data),
			);
	}
}
