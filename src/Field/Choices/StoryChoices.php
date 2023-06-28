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
		private readonly ComponentFilter $components = new ComponentFilter(),
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
			"filter_content_type" => new ResolvableComponentFilter($this->components, "filter_content_type"),
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
			\array_map(
				fn (string $uuid) => new StoryReferenceData($uuid, $this->referencedStoryDataMode),
				$data,
			),
		);
	}
}
