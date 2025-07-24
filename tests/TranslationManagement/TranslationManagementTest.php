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
	private array $componentFieldConfiguration = [
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
	];

	private function getStoryOriginal () : array
	{
		$storyJsonOriginal = file_get_contents(\sprintf("%s/Data/StoryOriginal.json", __DIR__));
		$storyOriginal = json_decode($storyJsonOriginal, true);

		\assert(\is_array($storyOriginal));

		return $storyOriginal;
	}

	private function getStoryTranslated () : array
	{
		$jsonStoryTranslated = file_get_contents(\sprintf("%s/Data/StoryTranslated.json", __DIR__));
		$storyTranslated = json_decode($jsonStoryTranslated, true);

		\assert(\is_array($storyTranslated));

		return $storyTranslated;
	}

	private function getXliffOriginal () : string
	{
		return file_get_contents(\sprintf("%s/Data/StoryTranslationXliffOriginal.xml", __DIR__)) ?: throw new \RuntimeException("Could not load xliff file");
	}

	private function getXliffTranslated () : string
	{
		return file_get_contents(\sprintf("%s/Data/StoryTranslationXliffTranslated.xml", __DIR__)) ?: throw new \RuntimeException("Could not load xliff file");
	}

	/**
	 */
	public function testStoryToXliff () : void
	{
		$story = $this->getStoryOriginal();
		$xliffExpected = $this->getXliffOriginal();

		$xliffNormalizer = new XliffNormalizer();

		$translatableContentExtractor
			= new TranslatableContentExtractor(new RichTextHtmlTransformer(new FixBrokenLinksMarksHelper()));

		$xliff = $xliffNormalizer->normalize(
			$translatableContentExtractor->extractTranslatableContent(
				$story,
				$this->componentFieldConfiguration,
			),
			[
				"targetLanguage" => "en",
			],
		);

		self::assertSame($xliffExpected, $xliff);
	}

	/**
	 */
	public function testStoryTranslation () : void
	{
		$story = $this->getStoryOriginal();
		$storyTranslatedExpected = $this->getStoryTranslated();
		$xliffTranslated = $this->getXliffTranslated();
		$languageExpected = "en";

		$xliffNormalizer = new XliffNormalizer();
		$translatableContentCollection = $xliffNormalizer->denormalize($xliffTranslated);

		self::assertSame($languageExpected, $translatableContentCollection->getLanguage());

		$translatableContentTranslator = new TranslatableContentTranslator(new RichTextHtmlTransformer(new FixBrokenLinksMarksHelper()));
		$storyTranslated = $translatableContentTranslator->translate($story, $translatableContentCollection);

		self::assertSame($storyTranslatedExpected, $storyTranslated);
	}
}
