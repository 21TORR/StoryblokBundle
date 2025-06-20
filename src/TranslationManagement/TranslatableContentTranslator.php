<?php declare(strict_types=1);

namespace Torr\Storyblok\TranslationManagement;

use JsonPath\InvalidJsonException;
use JsonPath\JsonObject;
use Torr\Storyblok\Tiptap\Transformer\RichTextHtmlTransformer;
use Torr\Storyblok\TranslationManagement\Data\TranslatableComponentData;
use Torr\Storyblok\TranslationManagement\Data\TranslatableContentCollection;
use Torr\Storyblok\TranslationManagement\Exception\StoryUpdateException;
use Torr\Storyblok\TranslationManagement\Exception\TranslationManagementExceptionInterface;

final readonly class TranslatableContentTranslator
{
	public function __construct (
		private RichTextHtmlTransformer $richTextHtmlTransformer,
	) {}

	/**
	 * @param array $story Story data from storyblok management api
	 *
	 * @return array Updated story data for storyblok management api
	 *
	 * @throws TranslationManagementExceptionInterface
	 * @throws InvalidJsonException
	 */
	public function translate (array $story, TranslatableContentCollection $translatableContentCollection) : array
	{
		$jsonObject = new JsonObject($story);

		foreach ($translatableContentCollection as $translatableContent)
		{
			$jsonDataForKey = $jsonObject->get($translatableContent->getKey());

			if (!$jsonDataForKey)
			{
				// skip if text was removed
				continue;
			}

			if (
				$translatableContent->getValue()
				&& \is_array($jsonDataForKey)
				&& TranslatableComponentData::isRichText($jsonDataForKey[0] ?? null)
			)
			{
				$jsonObject->set(
					$translatableContent->getKey(),
					json_decode(
						$this->richTextHtmlTransformer->transformToJsonMarkup($translatableContent->getValue()),
						true,
						flags: \JSON_THROW_ON_ERROR,
					),
				);

				continue;
			}

			$jsonObject->set($translatableContent->getKey(), $translatableContent->getValue());
		}

		return $jsonObject->getValue() ?? throw new StoryUpdateException("Story update failed");
	}
}
