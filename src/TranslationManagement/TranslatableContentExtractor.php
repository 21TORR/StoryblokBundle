<?php declare(strict_types=1);

namespace Torr\Storyblok\TranslationManagement;

use Torr\Storyblok\TranslationManagement\Data\TranslatableComponentDataCollection;
use Torr\Storyblok\TranslationManagement\Data\TranslatableContentCollection;
use Torr\Storyblok\TranslationManagement\Data\TranslatableContentElement;
use Torr\Storyblok\TranslationManagement\Exception\StoryInvalidException;
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
	public static function extractTranslatableContent (array $story, array $config) : TranslatableContentCollection
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
}
