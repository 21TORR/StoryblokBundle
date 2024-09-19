<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\RichText;

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
}
