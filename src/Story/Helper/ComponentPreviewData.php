<?php declare(strict_types=1);

namespace Torr\Storyblok\Story\Helper;

use Torr\Storyblok\Component\Data\ComponentData;
use Torr\Storyblok\Story\Preview\PreviewDataParser;
use Torr\Storyblok\Story\StoryMetaData;

final class ComponentPreviewData
{
	public static function normalizeData (StoryMetaData|ComponentData $metaData) : array
	{
		if ($metaData instanceof ComponentData)
		{
			return [
				"_uid" => $metaData->uid,
				"_type" => $metaData->type,
				"_preview" => PreviewDataParser::parse($metaData->previewData),
			];
		}

		return [
			"_uid" => $metaData->getUuid(),
			"_type" => $metaData->getType(),
			"_preview" => PreviewDataParser::parse($metaData->getPreviewData()),
		];
	}
}
