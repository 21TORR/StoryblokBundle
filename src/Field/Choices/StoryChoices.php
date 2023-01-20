<?php declare(strict_types=1);

namespace Torr\Storyblok\Field\Choices;

use Torr\Storyblok\Component\Reference\ComponentsWithTags;
use Torr\Storyblok\Context\ComponentContext;

/**
 * Makes a story selectable
 */
final class StoryChoices implements ChoicesInterface
{
	/**
	 * @param array<string>|ComponentsWithTags $restrictContentTypes
	 */
	public function __construct (
		private readonly array|ComponentsWithTags $restrictContentTypes = [],
		private readonly string $restrictToPath = "",
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
	 * @inheritDoc
	 */
	public function transformData (
		ComponentContext $context,
		array|int|string $data,
	) : mixed
	{
		return $data;
	}
}
