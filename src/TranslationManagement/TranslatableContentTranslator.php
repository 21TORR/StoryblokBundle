<?php declare(strict_types=1);

namespace Torr\Storyblok\TranslationManagement;

use JsonPath\InvalidJsonException;
use JsonPath\JsonObject;
use Torr\Storyblok\TranslationManagement\Data\TranslatableContentCollection;
use Torr\Storyblok\TranslationManagement\Exception\StoryUpdateException;
use Torr\Storyblok\TranslationManagement\Exception\TranslationManagementExceptionInterface;

final class TranslatableContentTranslator
{
	/**
	 * @param array $story Story data from storyblok management api
	 *
	 * @return array Updated story data for storyblok management api
	 *
	 * @throws TranslationManagementExceptionInterface
	 * @throws InvalidJsonException
	 */
	public static function translate (array $story, TranslatableContentCollection $translatableContentCollection) : array
	{
		$jsonObject = new JsonObject($story);

		foreach ($translatableContentCollection as $translatableContent)
		{
			if (!$jsonObject->get($translatableContent->getKey()))
			{
				// skip if text was removed
				continue;
			}

			$jsonObject->set($translatableContent->getKey(), $translatableContent->getValue());
		}

		return $jsonObject->getValue() ?? throw new StoryUpdateException("Story update failed");
	}
}
