<?php declare(strict_types=1);

namespace Torr\Storyblok\Field\Choices;

use Torr\Storyblok\Component\Reference\ComponentsWithTags;
use Torr\Storyblok\Context\ComponentContext;
use Torr\Storyblok\Field\Data\StoryReferenceData;

/**
 * Makes a story selectable
 */
final class StoryChoices implements ChoicesInterface
{
	/**
	 * @param array<string>|ComponentsWithTags $restrictContentTypes
	 * @param string|\BackedEnum|null          $referencedStoryDataMode This is a Key that will be used by the corresponding StoryNormalizer to find out which data from the referenced Story is needed by the component
	 */
	public function __construct (
		private readonly array|ComponentsWithTags $restrictContentTypes = [],
		private readonly string $restrictToPath = "",
		private readonly string|\BackedEnum|null $referencedStoryDataMode = null,
	) {}

	/**
	 * @inheritDoc
	 */
	public function toManagementApiData () : array
	{
		return [
			"source" => "internal_stories",
			"filter_content_type" => $this->restrictContentTypes,
			"folder_slug" => $this->restrictToPath,
		];
	}

	/**
	 * @inheritDoc
	 */
	public function isValidData (
		array|int|string $data,
		?ComponentContext $context = null,
	) : bool
	{
		return true;
	}

	/**
	 * @return StoryReferenceData|array<StoryReferenceData>
	 */
	public function transformData (
		ComponentContext $context,
		array|int|string $data,
	) : array|StoryReferenceData
	{
		return new StoryReferenceData($data, $this->referencedStoryDataMode);
	}
}
