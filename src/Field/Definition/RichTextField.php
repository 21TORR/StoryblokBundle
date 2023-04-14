<?php declare(strict_types=1);

namespace Torr\Storyblok\Field\Definition;

use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Type;
use Torr\Storyblok\Component\Reference\ComponentsWithTags;
use Torr\Storyblok\Context\ComponentContext;
use Torr\Storyblok\Exception\InvalidFieldConfigurationException;
use Torr\Storyblok\Field\FieldType;
use Torr\Storyblok\Field\RichText\RichTextStyling;
use Torr\Storyblok\Visitor\DataVisitorInterface;

final class RichTextField extends AbstractField
{
	/**
	 * @inheritDoc
	 *
	 * @param array<string>|ComponentsWithTags $filterComponents
	 * @param array<string>                    $filterComponentGroups
	 * @param array<RichTextStyling>           $toolbarOptions
	 * @param array<string, string>            $styleOptions
	 */
	public function __construct (
		string $label,
		mixed $defaultValue = null,
		private readonly ?int $maxLength = null,
		private readonly array|ComponentsWithTags $filterComponents = [],
		private readonly array $filterComponentGroups = [],
		private readonly array $toolbarOptions = [],
		private readonly array $styleOptions = [],
	)
	{
		parent::__construct($label, $defaultValue);

		if (!empty($this->filterComponents) && !empty($this->filterComponentGroups))
		{
			throw new InvalidFieldConfigurationException("You can't filter both component groups and components");
		}
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

		return \array_replace(
			parent::toManagementApiData(),
			[
				"max_length" => $this->maxLength,
				"customize_toolbar" => !empty($this->toolbarOptions),
				"toolbar" => \array_map(
					static fn (RichTextStyling $option) => $option->value,
					$this->toolbarOptions,
				),
				"restrict_type" => !empty($this->filterComponentGroups) ? "groups" : "",
				"restrict_components" => !empty($this->filterComponents) || !empty($this->filterComponentGroups),
				"component_whitelist" => $this->filterComponents,
				"component_group_whitelist" => $this->filterComponentGroups,
				"style_options" => $formattedStyleOptions,
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
				new Type("array"),
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
		\assert(null === $data || \is_array($data));

		$transformed = null !== $data && !$this->contentIsEmpty($data)
			? $data
			: null;

		$content = $transformed["content"] ?? null;

		if (\is_array($content))
		{
			$transformed["_bloks"] = $this->fetchBlockDataMap(
				$transformed["content"],
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

		return 0 === \count($firstItemContent) &&
			\in_array($firstItemType, ["paragraph", "heading"], true);
	}
}
