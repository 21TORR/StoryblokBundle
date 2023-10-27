<?php declare(strict_types=1);

namespace Torr\Storyblok\Story\Preview;

use Torr\Storyblok\Exception\Story\InvalidDataException;

final class PreviewDataParser
{
	public static function parse (mixed $editableData) : ?array
	{
		if (!\is_string($editableData) && null !== $editableData)
		{
			throw new InvalidDataException(\sprintf(
				"Encountered invalid preview data of type '%s'. Expected string or null.",
				\get_debug_type($editableData),
			));
		}

		if (null === $editableData)
		{
			return null;
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
