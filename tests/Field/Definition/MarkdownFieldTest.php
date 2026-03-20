<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Field\Definition;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Torr\Storyblok\Field\Definition\MarkdownField;
use Torr\Storyblok\Management\ManagementApiData;

/**
 * @internal
 */
final class MarkdownFieldTest extends TestCase
{
	private static function getApiData (MarkdownField $field) : array
	{
		$apiData = new ManagementApiData();
		$field->registerManagementApiData("field", $apiData);

		return $apiData->getFullConfig()["field"];
	}

	public static function provideManagementApiData () : iterable
	{
		yield "defaults" => [
			new MarkdownField("My Label"),
			[
				"type" => "markdown",
				"display_name" => "My Label",
				"default_value" => null,
				"description" => null,
				"tooltip" => false,
				"translatable" => false,
				"required" => false,
				"regex" => null,
				"rich_markdown" => true,
				"rtl" => false,
				"max_length" => null,
				"allow_multiline" => true,
				"no_translate" => false,
			],
		];

		yield "no rich markdown" => [
			new MarkdownField("label", hasRichMarkdown: false),
			["rich_markdown" => false],
		];

		yield "max length" => [
			new MarkdownField("label", maxLength: 500),
			["max_length" => 500],
		];

		yield "rtl" => [
			new MarkdownField("label", isRightToLeft: true),
			["rtl" => true],
		];

		yield "no translate when export disabled" => [
			new MarkdownField("label", exportTranslation: false),
			["no_translate" => true],
		];

		yield "disallow multiline" => [
			new MarkdownField("label", allowMultiline: false),
			["allow_multiline" => false],
		];
	}

	/**
	 * @param array<string, mixed> $expected
	 */
	#[DataProvider("provideManagementApiData")]
	public function testManagementApiData (MarkdownField $field, array $expected) : void
	{
		$actual = self::getApiData($field);

		foreach ($expected as $key => $value)
		{
			self::assertSame($value, $actual[$key] ?? null, "Mismatch for key '{$key}'");
		}
	}
}
