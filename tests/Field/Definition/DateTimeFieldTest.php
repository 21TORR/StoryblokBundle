<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Field\Definition;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Torr\Storyblok\Field\Definition\DateTimeField;
use Torr\Storyblok\Management\ManagementApiData;

/**
 * @internal
 */
final class DateTimeFieldTest extends TestCase
{
	private static function getApiData (DateTimeField $field) : array
	{
		$apiData = new ManagementApiData();
		$field->registerManagementApiData("field", $apiData);

		return $apiData->getFullConfig()["field"];
	}

	public static function provideManagementApiData () : iterable
	{
		yield "defaults" => [
			new DateTimeField("My Label"),
			[
				"type" => "datetime",
				"display_name" => "My Label",
				"default_value" => null,
				"description" => null,
				"tooltip" => false,
				"translatable" => false,
				"required" => false,
				"regex" => null,
				// datetime is never exported for translation
				"no_translate" => true,
				// time is enabled by default
				"disable_time" => false,
			],
		];

		yield "without time selection" => [
			new DateTimeField("label", withTimeSelection: false),
			["disable_time" => true],
		];

		yield "default value" => [
			new DateTimeField("label", defaultValue: "2024-01-15 10:00"),
			["default_value" => "2024-01-15 10:00"],
		];
	}

	/**
	 * @param array<string, mixed> $expected
	 */
	#[DataProvider("provideManagementApiData")]
	public function testManagementApiData (DateTimeField $field, array $expected) : void
	{
		$actual = self::getApiData($field);

		foreach ($expected as $key => $value)
		{
			self::assertSame($value, $actual[$key] ?? null, "Mismatch for key '{$key}'");
		}
	}
}
