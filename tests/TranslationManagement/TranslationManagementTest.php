<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\TranslationManagement;

use PHPUnit\Framework\TestCase;
use Torr\Storyblok\Tiptap\Helper\FixBrokenLinksMarksHelper;
use Torr\Storyblok\Tiptap\Transformer\RichTextHtmlTransformer;
use Torr\Storyblok\TranslationManagement\Data\TranslatableContentCollection;
use Torr\Storyblok\TranslationManagement\Data\TranslatableContentElement;
use Torr\Storyblok\TranslationManagement\Exception\XliffInvalidException;
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

	/**
	 */
	public function testDenormalizeInvalidXliffWithoutFileThrows () : void
	{
		$normalizer = new XliffNormalizer();

		$this->expectException(XliffInvalidException::class);
		$this->expectExceptionMessage("File tag missing");

		$normalizer->denormalize(<<<'XML'
			<?xml version="1.0" encoding="UTF-8"?>
			<xliff xmlns="urn:oasis:names:tc:xliff:document:2.0" version="2.1" srcLang="de" trgLang="en">
			</xliff>
			XML);
	}

	/**
	 */
	public function testDenormalizeInvalidXliffWithoutTargetThrows () : void
	{
		$normalizer = new XliffNormalizer();

		$this->expectException(XliffInvalidException::class);
		$this->expectExceptionMessage("Tag target missing in unit");

		$normalizer->denormalize(<<<'XML'
			<?xml version="1.0" encoding="UTF-8"?>
			<xliff xmlns="urn:oasis:names:tc:xliff:document:2.0" version="2.1" srcLang="de" trgLang="en">
				<file id="1" original="root/test">
					<unit id="$..['content']">
						<segment>
							<source>text</source>
						</segment>
					</unit>
				</file>
			</xliff>
			XML);
	}

	/**
	 */
	public function testTranslateSkipsMissingPath () : void
	{
		$story = [
			"id" => 1,
			"content" => [
				"body" => [
					[
						"_uid" => "component-1",
						"component" => "text-block",
						"text" => "Original",
					],
				],
			],
		];

		$collection = new TranslatableContentCollection(
			id: "1",
			url: "root/test",
			language: "en",
			data: [
				new TranslatableContentElement("$..[?(@['_uid']=='missing')]['text']", "Updated"),
			],
		);

		$translator = new TranslatableContentTranslator(new RichTextHtmlTransformer(new FixBrokenLinksMarksHelper()));
		$translated = $translator->translate($story, $collection);

		self::assertSame($story, $translated);
	}

	/**
	 */
	public function testTranslateRichTextField () : void
	{
		$story = [
			"id" => 1,
			"content" => [
				"body" => [
					[
						"_uid" => "component-1",
						"component" => "text-block",
						"text" => [
							"type" => "doc",
							"content" => [
								[
									"type" => "paragraph",
									"content" => [
										[
											"type" => "text",
											"text" => "Original",
										],
									],
								],
							],
						],
					],
				],
			],
		];

		$collection = new TranslatableContentCollection(
			id: "1",
			url: "root/test",
			language: "en",
			data: [
				new TranslatableContentElement(
					"$..[?(@['_uid']=='component-1')]['text']",
					"<p>Updated</p>",
				),
			],
		);

		$translator = new TranslatableContentTranslator(new RichTextHtmlTransformer(new FixBrokenLinksMarksHelper()));
		$translated = $translator->translate($story, $collection);

		$richTextData = $translated["content"]["body"][0]["text"];

		self::assertIsArray($richTextData);
		self::assertSame("doc", $richTextData["type"]);
		self::assertSame("Updated", $richTextData["content"][0]["content"][0]["text"]);
	}
}
