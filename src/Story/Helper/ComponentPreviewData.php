<?php declare(strict_types=1);

namespace Torr\Storyblok\Story\Helper;

use Torr\Storyblok\Component\Data\ComponentData;
use Torr\Storyblok\Exception\Story\InvalidDataException;
use Torr\Storyblok\Story\MetaData\DocumentMetaData;

final class ComponentPreviewData
{
	/**
	 * Normalizes the preview-related data to return it directly
	 */
	public static function normalizeData (DocumentMetaData|ComponentData $metaData) : array
	{
		if ($metaData instanceof ComponentData)
		{
			return [
				"_uid" => $metaData->uid,
				"_type" => $metaData->type,
				"_preview" => self::parsePreviewTag($metaData->previewData),
			];
		}

		return [
			"_uid" => $metaData->getUuid(),
			"_type" => $metaData->getType(),
			"_preview" => self::parsePreviewTag($metaData->getPreviewData()),
		];
	}

	/**
	 */
	private static function parsePreviewTag (mixed $editableData) : ?array
	{
		if (null === $editableData)
		{
			return null;
		}

		if (!\is_string($editableData))
		{
			throw new InvalidDataException(\sprintf(
				"Encountered invalid preview data of type '%s'. Expected string or null.",
				\get_debug_type($editableData),
			));
		}

		$previewData = null;

		if (\preg_match('~^<!--#storyblok#(.*)-->$~', $editableData, $matches))
		{
			try
			{
				$previewData = \json_decode(\stripslashes($matches[1]), true, flags: \JSON_THROW_ON_ERROR);

				\assert(\is_array($previewData));
			}
			catch (\JsonException $exception)
			{
				throw new InvalidDataException(\sprintf(
					"Encountered invalid preview data: '%s'",
					$editableData,
				), previous: $exception);
			}
		}

		return $previewData;
	}
}
