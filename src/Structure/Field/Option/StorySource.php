<?php declare(strict_types=1);

namespace Torr\Storyblok\Structure\Field\Option;

use Torr\Storyblok\Structure\Component\ComponentTypeDefinition;

final class StorySource implements ChoiceSourceInterface
{
	/**
	 * @param array<class-string<ComponentTypeDefinition>> $restrictContentTypes
	 */
	public function __construct (
		private readonly array $restrictContentTypes = [],
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
}
