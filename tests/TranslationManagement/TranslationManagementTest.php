<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\TranslationManagement;

use PHPUnit\Framework\TestCase;
use Torr\Storyblok\Tiptap\Helper\FixBrokenLinksMarksHelper;
use Torr\Storyblok\Tiptap\Transformer\RichTextHtmlTransformer;
use Torr\Storyblok\TranslationManagement\Normalizer\XliffNormalizer;
use Torr\Storyblok\TranslationManagement\TranslatableContentExtractor;
use Torr\Storyblok\TranslationManagement\TranslatableContentTranslator;

/**
 * @internal
 */
final class TranslationManagementTest extends TestCase
{
	/**
	 */
	public function testBasic () : void
	{
		$xliffNormalizer = new XliffNormalizer();

		$translatableContentExtractor = new TranslatableContentExtractor(new RichTextHtmlTransformer(new FixBrokenLinksMarksHelper()));

		$jsonOriginal = file_get_contents(\sprintf("%s/Data/StoryOriginal.json", __DIR__));
		$story = json_decode($jsonOriginal, true);

		$transformedStoryXliff = $xliffNormalizer->normalize(
			$translatableContentExtractor->extractTranslatableContent(
				$story,
				[
					"product" => [
						"name",
						"description",
					],
					"quote-block" => [
						"quote",
						"author",
						"anchor-title",
					],
					"text-block" => [
						"text",
						"anchor-title",
					],
				],
			),
			[
				"targetLanguage" => "en",
			],
		);

		$xliffOriginal = file_get_contents(\sprintf("%s/Data/StoryTranslationXliffOriginal.xml", __DIR__));

		self::assertSame($xliffOriginal, $transformedStoryXliff);

		$jsonTranslated = file_get_contents(\sprintf("%s/Data/StoryTranslated.json", __DIR__));
		$storyTranslated = json_decode($jsonTranslated, true);

		$xliffTranslated = file_get_contents(\sprintf("%s/Data/StoryTranslationXliffTranslated.xml", __DIR__));

		$translatableContentCollection = $xliffNormalizer->denormalize($xliffTranslated);

		self::assertSame("en", $translatableContentCollection->getLanguage());

		$translatableContentTranslator = new TranslatableContentTranslator(new RichTextHtmlTransformer(new FixBrokenLinksMarksHelper()));

		$updatedStory = $translatableContentTranslator->translate($story, $translatableContentCollection);

		self::assertSame($storyTranslated, $updatedStory);
	}
}
