<?php declare(strict_types=1);

namespace Torr\Storyblok\Exception\Api;

final class TranslationsXmlFileImportFailedException extends \RuntimeException implements ApiRequestException
{
	public function __construct (
		string $storyId,
		?\Throwable $previous = null,
	)
	{
		parent::__construct(
			"An exception occurred during the import of the the XML Translations file for Story {$storyId}.",
			500,
			$previous,
		);
	}
}
