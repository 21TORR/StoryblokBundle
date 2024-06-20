<?php declare(strict_types=1);

namespace Torr\Storyblok\Field\Choices;

use Torr\Storyblok\Component\Filter\ComponentFilter;
use Torr\Storyblok\Context\ComponentContext;
use Torr\Storyblok\Field\Data\Story\StoryReferenceList;
use Torr\Storyblok\Field\Data\StoryReferenceData;
use Torr\Storyblok\Manager\Sync\Filter\ResolvableComponentFilter;

/**
 * Makes a story selectable
 */
final class StoryChoices implements ChoicesInterface
{
	/**
	 * @param string|\BackedEnum|null $referencedStoryDataMode This is a Key that will be used by the corresponding StoryNormalizer to find out which data from the referenced Story is needed by the component
	 */
	public function __construct (
		private readonly ComponentFilter $allowedComponents = new ComponentFilter(),
		private readonly string $restrictToPath = "",
		private readonly string|\BackedEnum|null $referencedStoryDataMode = null,
		private readonly bool $displayAsCard = false,
		private readonly bool $allowAdvancedSearch = false,
	) {}

	/**
	 * @inheritDoc
	 */
	public function toManagementApiData () : array
	{
		return [
			"source" => "internal_stories",
			"filter_content_type" => new ResolvableComponentFilter($this->allowedComponents, "filter_content_type"),
			"folder_slug" => $this->restrictToPath,
			"entry_appearance" => $this->displayAsCard ? "card" : "link",
			"allow_advanced_search" => $this->allowAdvancedSearch,
		];
	}

	/**
	 * @inheritDoc
	 */
	public function getValidationConstraints (bool $allowMultiple) : array
	{
		// always valid
		return [];
	}

	/**
	 */
	public function transformData (
		ComponentContext $context,
		array|int|string $data,
	) : StoryReferenceList|StoryReferenceData
	{
		if (!\is_array($data))
		{
			return new StoryReferenceData((string) $data, $this->referencedStoryDataMode);
		}

		return new StoryReferenceList(
			array_map(
				fn (string $uuid) => new StoryReferenceData($uuid, $this->referencedStoryDataMode),
				$data,
			),
		);
	}
}
