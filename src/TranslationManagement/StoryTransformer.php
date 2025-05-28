<?php declare(strict_types=1);

namespace Torr\Storyblok\TranslationManagement;

use Torr\Storyblok\TranslationManagement\Data\ComponentDataCollection;
use Torr\Storyblok\TranslationManagement\Data\TranslationDataCollection;
use Torr\Storyblok\TranslationManagement\Data\TranslationDataElement;
use Torr\Storyblok\TranslationManagement\Exception\StoryInvalidException;
use Torr\Storyblok\TranslationManagement\Service\NormalizerInterface;
use Torr\Storyblok\TranslationManagement\Service\XmlNormalizer;
use Torr\Storyblok\TranslationManagement\Validator\StoryValidator;

final class StoryTransformer
{
	/**
	 * @param array                       $story  Storyblok management API story data
	 * @param array<string, list<string>> $config
	 *
	 * @throws StoryInvalidException
	 */
	public function transform (array $story, array $config, string $languageCode = "default", NormalizerInterface $normalizer = new XmlNormalizer()) : string
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
						$tagElements[] = new TranslationDataElement(
							$richTextValue["key"],
							"STRING",
							$richTextValue["value"],
						);
					}

					continue;
				}

				$tagElements[] = new TranslationDataElement(
					$componentData->getKeyForField($fieldname),
					"STRING",
					$componentData->getStringValueForField($fieldname),
				);
			}
		}

		$translationDataCollection = new TranslationDataCollection(
			(string) $story["id"],
			$story["slug"],
			$story["full_slug"],
			$languageCode,
			$story["name"],
			$tagElements,
		);

		return $translationDataCollection->normalize($normalizer);
	}
}
