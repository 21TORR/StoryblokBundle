<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\RichText;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Torr\Storyblok\RichText\HtmlToRichTextTransformer;

/**
 * @internal
 */
final class HtmlToRichTextTransformerTest extends TestCase
{
	/**
	 */
	public function testBasic () : void
	{
		$transformer = new HtmlToRichTextTransformer();

		$html = <<<'HTML'
				<p>Test</p>
				<ul>
					<li>UL List item</li>
				</ul>
				<ol>
					<li>OL List item</li>
				</ol>
				<h1>Heading 1</h1>
				<h2>Heading 2</h2>
				<h3>Heading 3</h3>
				<h4>Heading 4</h4>
				<h5>Heading 5</h5>
				<h6>Heading 6</h6>
				<p>Text with
					<strong>bold 1</strong>
					<b>bold 2</b>
					<em>italic 1</em>
					<i>italic 2</i>
					<a href="/test" target="_blank">External Link</a>
					<a href="/test">Link</a>
					<mark>highlight</mark>
					<mark data-color="#ef2b7c">highlight with color</mark>
					<sup>superscript</sup>
					<sub>subscript</sub>
					<u>underline</u>
					<br>
					<code>code</code>
				</p>
				<hr>
			HTML;

		// use assertSame() as the order is important
		self::assertSame([
			"type" => "doc",
			"content" => [
				[
					"type" => "paragraph",
					"content" => [
						[
							'type' => 'text',
							'text' => 'Test',
						],
					],
				],
				[
					"type" => "bullet_list",
					"content" => [
						[
							'type' => 'list_item',
							'content' => [
								[
									"type" => "paragraph",
									"content" => [
										[
											'type' => 'text',
											'text' => 'UL List item',
										],
									],
								],
							],
						],
					],
				],
				[
					"type" => "ordered_list",
					"content" => [
						[
							'type' => 'list_item',
							'content' => [
								[
									"type" => "paragraph",
									"content" => [
										[
											'type' => 'text',
											'text' => 'OL List item',
										],
									],
								],
							],
						],
					],
				],
				[
					"type" => "heading",
					"attrs" => [
						"level" => 1,
					],
					"content" => [
						[
							'type' => 'text',
							'text' => 'Heading 1',
						],
					],
				],
				[
					"type" => "heading",
					"attrs" => [
						"level" => 2,
					],
					"content" => [
						[
							'type' => 'text',
							'text' => 'Heading 2',
						],
					],
				],
				[
					"type" => "heading",
					"attrs" => [
						"level" => 3,
					],
					"content" => [
						[
							'type' => 'text',
							'text' => 'Heading 3',
						],
					],
				],
				[
					"type" => "heading",
					"attrs" => [
						"level" => 4,
					],
					"content" => [
						[
							'type' => 'text',
							'text' => 'Heading 4',
						],
					],
				],
				[
					"type" => "heading",
					"attrs" => [
						"level" => 5,
					],
					"content" => [
						[
							'type' => 'text',
							'text' => 'Heading 5',
						],
					],
				],
				[
					"type" => "heading",
					"attrs" => [
						"level" => 6,
					],
					"content" => [
						[
							'type' => 'text',
							'text' => 'Heading 6',
						],
					],
				],
				[
					"type" => "paragraph",
					"content" => [
						[
							"type" => "text",
							"text" => "Text with\n",
						],
						[
							"type" => "text",
							"text" => "bold 1",
							"marks" => [
								["type" => "bold"],
							],
						],
						[
							"type" => "text",
							"text" => "bold 2",
							"marks" => [
								["type" => "bold"],
							],
						],
						[
							"type" => "text",
							"text" => "italic 1",
							"marks" => [
								["type" => "italic"],
							],
						],
						[
							"type" => "text",
							"text" => "italic 2",
							"marks" => [
								["type" => "italic"],
							],
						],
						[
							"type" => "text",
							"text" => "External Link",
							"marks" => [
								[
									"type" => "link",
									"attrs" => [
										"href" => "/test",
										"target" => "_blank",
									],
								],
							],
						],
						[
							"type" => "text",
							"text" => "Link",
							"marks" => [
								[
									"type" => "link",
									"attrs" => [
										"href" => "/test",
									],
								],
							],
						],
						[
							"type" => "text",
							"text" => "highlight",
							"marks" => [
								["type" => "highlight"],
							],
						],
						[
							"type" => "text",
							"text" => "highlight with color",
							"marks" => [
								[
									"type" => "highlight",
									"attrs" => [
										"color" => "#ef2b7c",
									],
								],
							],
						],
						[
							"type" => "text",
							"text" => "superscript",
							"marks" => [
								["type" => "superscript"],
							],
						],
						[
							"type" => "text",
							"text" => "subscript",
							"marks" => [
								["type" => "subscript"],
							],
						],
						[
							"type" => "text",
							"text" => "underline",
							"marks" => [
								["type" => "underline"],
							],
						],
						[
							"type" => "hard_break",
						],
						[
							"type" => "text",
							"text" => "code",
							"marks" => [
								["type" => "code"],
							],
						],
					],
				],
				[
					"type" => "horizontal_rule",
				],
			],
		], $transformer->parseHtmlToRichText($html));
	}

	/**
	 */
	public static function provideListItems () : iterable
	{
		yield "inline text is wrapped in a paragraph" => [
			"<ul><li>Test</li></ul>",
			self::bulletList(self::listItem(self::paragraph(self::text("Test")))),
		];

		yield "single paragraph is kept as is" => [
			"<ul><li><p>Test</p></li></ul>",
			self::bulletList(self::listItem(self::paragraph(self::text("Test")))),
		];

		yield "multiple paragraphs are not nested in another paragraph" => [
			"<ul><li><p>A</p><p>B</p></li></ul>",
			self::bulletList(self::listItem(
				self::paragraph(self::text("A")),
				self::paragraph(self::text("B")),
			)),
		];

		yield "consecutive inline nodes with marks and hard breaks are wrapped in a single paragraph" => [
			"<ul><li><strong>Bold</strong> text<br>next</li></ul>",
			self::bulletList(self::listItem(self::paragraph(
				[
					"type" => "text",
					"text" => "Bold",
					"marks" => [
						["type" => "bold"],
					],
				],
				self::text(" text"),
				["type" => "hard_break"],
				self::text("next"),
			))),
		];

		yield "nested list is not wrapped in a paragraph" => [
			"<ul><li>A<ul><li>B</li></ul></li></ul>",
			self::bulletList(self::listItem(
				self::paragraph(self::text("A")),
				self::bulletList(self::listItem(self::paragraph(self::text("B")))),
			)),
		];

		yield "inline text next to a paragraph gets its own paragraph" => [
			"<ul><li>A<p>B</p>C</li></ul>",
			self::bulletList(self::listItem(
				self::paragraph(self::text("A")),
				self::paragraph(self::text("B")),
				self::paragraph(self::text("C")),
			)),
		];

		yield "formatting whitespace around a paragraph is ignored" => [
			"<ul>\n\t<li>\n\t\t<p>A</p>\n\t</li>\n</ul>",
			self::bulletList(self::listItem(self::paragraph(self::text("A")))),
		];

		yield "ordered list" => [
			"<ol><li>Test</li></ol>",
			[
				"type" => "ordered_list",
				"content" => [
					self::listItem(self::paragraph(self::text("Test"))),
				],
			],
		];
	}

	/**
	 */
	#[DataProvider("provideListItems")]
	public function testListItems (string $html, array $expectedNode) : void
	{
		$transformer = new HtmlToRichTextTransformer();

		self::assertSame(
			[
				"type" => "doc",
				"content" => [$expectedNode],
			],
			$transformer->parseHtmlToRichText($html),
		);
	}

	/**
	 */
	private static function bulletList (array ...$items) : array
	{
		return [
			"type" => "bullet_list",
			"content" => $items,
		];
	}

	/**
	 */
	private static function listItem (array ...$content) : array
	{
		return [
			"type" => "list_item",
			"content" => $content,
		];
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
