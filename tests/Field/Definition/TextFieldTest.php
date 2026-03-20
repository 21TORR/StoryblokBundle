<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Field\Definition;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Torr\Storyblok\Field\Definition\TextField;
use Torr\Storyblok\Management\ManagementApiData;

/**
 * @internal
 */
final class TextFieldTest extends TestCase
{
	private static function getApiData (TextField $field) : array
	{
		$apiData = new ManagementApiData();
		$field->registerManagementApiData("field", $apiData);

		return $apiData->getFullConfig()["field"];
	}

	public static function provideManagementApiData () : iterable
	{
		yield "defaults" => [
			new TextField("My Label"),
			[
				"type" => "text",
				"display_name" => "My Label",
				"default_value" => null,
				"description" => null,
				"tooltip" => false,
				"translatable" => false,
				"required" => false,
				"regex" => null,
				"rtl" => false,
				"max_length" => null,
				"no_translate" => false,
			],
		];

		yield "multiline becomes textarea" => [
			new TextField("label", multiline: true),
			["type" => "textarea"],
		];

		yield "default value" => [
			new TextField("label", defaultValue: "hello"),
			["default_value" => "hello"],
		];

		yield "max length" => [
			new TextField("label", maxLength: 100),
			["max_length" => 100],
		];

		yield "rtl" => [
			new TextField("label", isRightToLeft: true),
			["rtl" => true],
		];

		yield "no translate when export disabled" => [
			new TextField("label", exportTranslation: false),
			["no_translate" => true],
		];
	}

	/**
	 * @param array<string, mixed> $expected
	 */
	#[DataProvider("provideManagementApiData")]
	public function testManagementApiData (TextField $field, array $expected) : void
	{
		$actual = self::getApiData($field);

		foreach ($expected as $key => $value)
		{
			self::assertSame($value, $actual[$key] ?? null, "Mismatch for key '{$key}'");
		}
	}

	/**
	 * Tests base properties inherited from AbstractField (applicable to all field types)
	 */
	public function testBaseProperties () : void
	{
		$field = new TextField("My Label");
		$field->enableTranslations();
		$field->enableValidation(required: true, regexp: "^[a-z]+$");
		$field->addDescription("Help text", showAsTooltip: true);

		$actual = self::getApiData($field);

		self::assertSame(true, $actual["translatable"]);
		self::assertSame(true, $actual["required"]);
		self::assertSame("^[a-z]+$", $actual["regex"]);
		self::assertSame("Help text", $actual["description"]);
		self::assertSame(true, $actual["tooltip"]);
	}

	public function testDescriptionWithoutTooltip () : void
	{
		$field = new TextField("label");
		$field->addDescription("Help text");

		$actual = self::getApiData($field);

		self::assertSame("Help text", $actual["description"]);
		self::assertSame(false, $actual["tooltip"]);
	}
}
