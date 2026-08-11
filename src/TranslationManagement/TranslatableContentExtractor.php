<?php declare(strict_types=1);

namespace Torr\Storyblok\TranslationManagement;

use Torr\Storyblok\Tiptap\Transformer\RichTextHtmlTransformer;
use Torr\Storyblok\TranslationManagement\Data\TranslatableComponentDataCollection;
use Torr\Storyblok\TranslationManagement\Data\TranslatableContentCollection;
use Torr\Storyblok\TranslationManagement\Data\TranslatableContentElement;
use Torr\Storyblok\TranslationManagement\Exception\StoryInvalidException;
use Torr\Storyblok\TranslationManagement\Exception\TranslationManagementException;
use Torr\Storyblok\TranslationManagement\Validator\StoryValidator;

final readonly class TranslatableContentExtractor
{
	public function __construct (
		private RichTextHtmlTransformer $richTextHtmlTransformer,
	) {}

	/**
	 * @param array                       $story  Storyblok management API story data
	 * @param array<string, list<string>> $config <component-key, <fieldnames>>
	 *
	 * @throws TranslationManagementException
	 * @throws StoryInvalidException
	 * @throws \JsonException
	 */
	public function extractTranslatableContent (array $story, array $config) : TranslatableContentCollection
	{
		if (!StoryValidator::isValid($story))
		{
			throw new StoryInvalidException("Story is not valid");
		}

		$componentDataCollection = new TranslatableComponentDataCollection($story);

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
					$jsonRichTextData = $componentData->getJsonValueForField($fieldname);

					$tagElements[] = new TranslatableContentElement(
						$componentData->getKeyForField($fieldname),
						$jsonRichTextData ? $this->richTextHtmlTransformer->transformToHtml($jsonRichTextData) : null,
					);

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
			data: $tagElements,
		);
	}
}
