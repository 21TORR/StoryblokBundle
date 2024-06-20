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
			],
		], $transformer->parseHtmlToRichText($html));
	}
}
