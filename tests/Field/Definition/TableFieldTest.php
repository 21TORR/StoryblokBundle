<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Field\Definition;

use PHPUnit\Framework\TestCase;
use Torr\Storyblok\Field\Definition\TableField;
use Torr\Storyblok\Management\ManagementApiData;

/**
 * @internal
 */
final class TableFieldTest extends TestCase
{
	private static function getApiData (TableField $field) : array
	{
		$apiData = new ManagementApiData();
		$field->registerManagementApiData("field", $apiData);

		return $apiData->getFullConfig()["field"];
	}

	public function testDefaults () : void
	{
		$actual = self::getApiData(new TableField("My Label"));

		self::assertSame("table", $actual["type"]);
		self::assertSame("My Label", $actual["display_name"]);
		self::assertNull($actual["default_value"]);
		self::assertNull($actual["description"]);
		self::assertFalse($actual["tooltip"]);
		self::assertFalse($actual["translatable"]);
		self::assertFalse($actual["required"]);
		self::assertNull($actual["regex"]);
	}

	public function testDefaultValue () : void
	{
		$actual = self::getApiData(new TableField("label", defaultValue: "some-default"));

		self::assertSame("some-default", $actual["default_value"]);
	}
}
