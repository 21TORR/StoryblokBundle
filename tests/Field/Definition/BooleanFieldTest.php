<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Field\Definition;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Torr\Storyblok\Field\Definition\BooleanField;
use Torr\Storyblok\Management\ManagementApiData;

/**
 * @internal
 */
final class BooleanFieldTest extends TestCase
{
	private static function getApiData (BooleanField $field) : array
	{
		$apiData = new ManagementApiData();
		$field->registerManagementApiData("field", $apiData);

		return $apiData->getFullConfig()["field"];
	}

	public static function provideManagementApiData () : iterable
	{
		yield "defaults" => [
			new BooleanField("My Label"),
			[
				"type" => "boolean",
				"display_name" => "My Label",
				"default_value" => false,
				"description" => null,
				"tooltip" => false,
				"translatable" => false,
				"required" => false,
				"regex" => null,
				"inline_label" => null,
			],
		];

		yield "default value true" => [
			new BooleanField("label", defaultValue: true),
			["default_value" => true],
		];

		yield "inline label" => [
			new BooleanField("label", inlineLabel: "Enable feature"),
			["inline_label" => "Enable feature"],
		];
	}

	/**
	 * @param array<string, mixed> $expected
	 */
	#[DataProvider("provideManagementApiData")]
	public function testManagementApiData (BooleanField $field, array $expected) : void
	{
		$actual = self::getApiData($field);

		foreach ($expected as $key => $value)
		{
			self::assertSame($value, $actual[$key] ?? null, "Mismatch for key '{$key}'");
		}
	}
}
