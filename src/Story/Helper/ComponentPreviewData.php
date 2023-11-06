<?php declare(strict_types=1);

namespace Torr\Storyblok\Story\Helper;

use Torr\Storyblok\Exception\Story\InvalidDataException;
use Torr\Storyblok\Story\MetaData\ContentMetaData;

final class ComponentPreviewData
{
	/**
	 * Normalizes the preview-related data to return it directly
	 */
	public static function normalizeData (ContentMetaData $metaData) : array
	{
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
