<?php declare(strict_types=1);

namespace Torr\Storyblok\TranslationManagement;

use JsonPath\InvalidJsonException;
use JsonPath\JsonObject;
use Torr\Storyblok\TranslationManagement\Data\TranslatableComponentDataCollection;
use Torr\Storyblok\TranslationManagement\Data\TranslatableContentCollection;
use Torr\Storyblok\TranslationManagement\Data\TranslatableContentElement;
use Torr\Storyblok\TranslationManagement\Exception\StoryInvalidException;
use Torr\Storyblok\TranslationManagement\Exception\StoryUpdateException;
use Torr\Storyblok\TranslationManagement\Exception\TranslationManagementExceptionInterface;
use Torr\Storyblok\TranslationManagement\Validator\StoryValidator;

final class TranslatableContentExtractor
{
	/**
	 * @param array                       $story  Storyblok management API story data
	 * @param array<string, list<string>> $config <component-key, <fieldnames>>
	 *
	 * @throws TranslationManagementExceptionInterface
	 * @throws StoryInvalidException
	 */
	public function extractTranslatableContent (array $story, array $config) : TranslatableContentCollection
	{
		if (!StoryValidator::isValid($story))
		{
			throw new StoryInvalidException("Story is not valid");
		}

		$componentDataCollection = new TranslatableComponentDataCollection($story);

		$tagElements = [];

		foreach ($componentDataCollection as $componentData)
		{
			$componentConfig = $config[$componentData->getKey()] ?? null;

			if (null === $componentConfig)
			{
				continue;
			}

			foreach ($componentConfig as $fieldname)
			{
				if ($componentData->isRichTextField($fieldname))
				{
					foreach ($componentData->getRichTextValuesForField($fieldname) as $richTextValue)
					{
						$tagElements[] = new TranslatableContentElement(
							$richTextValue["key"],
							$richTextValue["value"],
						);
					}

					continue;
				}

				$tagElements[] = new TranslatableContentElement(
					$componentData->getKeyForField($fieldname),
					$componentData->getStringValueForField($fieldname),
				);
			}
		}

		return new TranslatableContentCollection(
			id: (string) $story["id"],
			url: $story["full_slug"],
			language: $story["lang"] ?? "default",
			name: $story["name"],
			data: $tagElements,
		);
	}

	/**
	 * @param array $story Story data from storyblok management api
	 *
	 * @return array Updated story data for storyblok management api
	 *
	 * @throws TranslationManagementExceptionInterface
	 * @throws InvalidJsonException
	 */
	public function updateStory (array $story, TranslatableContentCollection $translatableContentCollection) : array
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
