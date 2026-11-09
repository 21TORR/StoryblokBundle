<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Tiptap\Transformer;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Torr\Storyblok\Tiptap\Helper\FixBrokenLinksMarksHelper;
use Torr\Storyblok\Tiptap\Transformer\RichTextHtmlTransformer;

/**
 * @internal
 */
final class RichTextHtmlTransformerTest extends TestCase
{
	/**
	 * List items must not get an additional paragraph wrapper, as Storyblok itself generates `<li>Text</li>`.
	 */
	public static function provideListItemsToJsonMarkup () : iterable
	{
		yield "inline text is not wrapped in a paragraph" => [
			"<ul><li>Test</li></ul>",
			[
				self::text("Test"),
			],
		];

		yield "single paragraph is kept as is" => [
			"<ul><li><p>Test</p></li></ul>",
			[
				self::paragraph(self::text("Test")),
			],
		];

		yield "multiple paragraphs are not nested in another paragraph" => [
			"<ul><li><p>A</p><p>B</p></li></ul>",
			[
				self::paragraph(self::text("A")),
				self::paragraph(self::text("B")),
			],
		];

		yield "nested list is not wrapped in a paragraph" => [
			"<ul><li>A<ul><li>B</li></ul></li></ul>",
			[
				self::text("A"),
				[
					"type" => "bullet_list",
					"content" => [
						[
							"type" => "list_item",
							"content" => [
								self::text("B"),
							],
						],
					],
				],
			],
		];
	}

	/**
	 */
	#[DataProvider("provideListItemsToJsonMarkup")]
	public function testListItemsToJsonMarkup (string $html, array $expectedListItemContent) : void
	{
		$transformer = new RichTextHtmlTransformer(new FixBrokenLinksMarksHelper());

		self::assertSame(
			[
				"type" => "doc",
				"content" => [
					[
						"type" => "bullet_list",
						"content" => [
							[
								"type" => "list_item",
								"content" => $expectedListItemContent,
							],
						],
					],
				],
			],
			json_decode($transformer->transformToJsonMarkup($html), true, flags: \JSON_THROW_ON_ERROR),
		);
	}

	/**
	 */
	public static function provideListItemsToHtml () : iterable
	{
		yield "inline text" => [
			[
				self::text("Test"),
			],
			"<ul><li>Test</li></ul>",
		];

		yield "single paragraph" => [
			[
				self::paragraph(self::text("Test")),
			],
			"<ul><li><p>Test</p></li></ul>",
		];

		yield "multiple paragraphs" => [
			[
				self::paragraph(self::text("A")),
				self::paragraph(self::text("B")),
			],
			"<ul><li><p>A</p><p>B</p></li></ul>",
		];
	}

	/**
	 */
	#[DataProvider("provideListItemsToHtml")]
	public function testListItemsToHtml (array $listItemContent, string $expectedHtml) : void
	{
		$transformer = new RichTextHtmlTransformer(new FixBrokenLinksMarksHelper());

		$json = json_encode(
			[
				"type" => "doc",
				"content" => [
					[
						"type" => "bullet_list",
						"content" => [
							[
								"type" => "list_item",
								"content" => $listItemContent,
							],
						],
					],
				],
			],
			\JSON_THROW_ON_ERROR,
		);

		self::assertSame($expectedHtml, $transformer->transformToHtml($json));
	}

	/**
	 */
	private static function paragraph (array ...$content) : array
	{
		return [
			"type" => "paragraph",
			"content" => $content,
		];
	}

	/**
	 */
	private static function text (string $text) : array
	{
		return [
			"type" => "text",
			"text" => $text,
		];
	}
}
