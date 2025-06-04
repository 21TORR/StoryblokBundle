<?php declare(strict_types=1);

namespace Torr\Storyblok\TranslationManagement;

use JsonPath\InvalidJsonException;
use JsonPath\JsonObject;
use Torr\Storyblok\TranslationManagement\Data\ComponentDataCollection;
use Torr\Storyblok\TranslationManagement\Data\TranslatableContentCollection;
use Torr\Storyblok\TranslationManagement\Data\TranslatableContentElement;
use Torr\Storyblok\TranslationManagement\Exception\StoryInvalidException;
use Torr\Storyblok\TranslationManagement\Exception\StoryUpdateException;
use Torr\Storyblok\TranslationManagement\Exception\TranslationManagementExceptionInterface;
use Torr\Storyblok\TranslationManagement\Normalizer\NormalizerInterface;
use Torr\Storyblok\TranslationManagement\Normalizer\XmlNormalizer;
use Torr\Storyblok\TranslationManagement\Validator\StoryValidator;

final class TranslationManagement
{
	/**
	 * @param array                       $story  Storyblok management API story data
	 * @param array<string, list<string>> $config <component-key, <fieldnames>>
	 *
	 * @throws TranslationManagementExceptionInterface
	 * @throws StoryInvalidException
	 */
	public function transformStory (array $story, array $config, string $languageCode = "default", NormalizerInterface $normalizer = new XmlNormalizer()) : string
	{
		if (!StoryValidator::isValid($story))
		{
			throw new StoryInvalidException("Story is not valid");
		}

		$componentDataCollection = new ComponentDataCollection($story);

		$tagElements = [];

		foreach ($componentDataCollection->getData() as $componentData)
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
							"STRING",
							$richTextValue["value"],
						);
					}

					continue;
				}

				$tagElements[] = new TranslatableContentElement(
					$componentData->getKeyForField($fieldname),
					"STRING",
					$componentData->getStringValueForField($fieldname),
				);
			}
		}

		$translationDataCollection = new TranslatableContentCollection(
			(string) $story["id"],
			$story["slug"],
			$story["full_slug"],
			$languageCode,
			$story["name"],
			$tagElements,
		);

		return $translationDataCollection->normalize($normalizer);
	}

	/**
	 * @param array  $story           Story data from storyblok management api
	 * @param string $translationData Translation xml from transformStory function
	 *
	 * @return array Updated story data for storyblok management api
	 *
	 * @throws TranslationManagementExceptionInterface
	 * @throws InvalidJsonException
	 */
	public function updateStory (array $story, string $translationData, NormalizerInterface $normalizer = new XmlNormalizer()) : array
	{
		$jsonObject = new JsonObject($story);

		foreach ($normalizer->denormalize($translationData)->getData() as $translationData)
		{
			if (!$jsonObject->get($translationData->getKey()))
			{
				// skip if text was removed
				continue;
			}

			$jsonObject->set($translationData->getKey(), $translationData->getValue());
		}

		return $jsonObject->getValue() ?? throw new StoryUpdateException("Story update failed");
	}
}
