<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Field\Definition;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Torr\Storyblok\Context\ComponentContext;
use Torr\Storyblok\Exception\InvalidFieldConfigurationException;
use Torr\Storyblok\Exception\Story\InvalidDataException;
use Torr\Storyblok\Field\Definition\NumberField;
use Torr\Storyblok\Image\ImageDimensionsExtractor;
use Torr\Storyblok\Management\ManagementApiData;
use Torr\Storyblok\Manager\ComponentManager;
use Torr\Storyblok\Transformer\DataTransformer;
use Torr\Storyblok\Validator\DataValidator;

/**
 * @internal
 */
final class NumberFieldTest extends TestCase
{
	/**
	 */
	public function testMaxValueValidationInvalid () : void
	{
		$this->expectException(InvalidFieldConfigurationException::class);
		$this->expectExceptionMessage("Invalid number field config: the max value '10.5' should be min value '1' + a multiple of steps '1'");

		new NumberField(
			label: "label",
			minValue: 1,
			maxValue: 10.5,
			steps: 1,
		);
	}

	/**
	 */
	public function testNegativeDecimalInvalid () : void
	{
		$this->expectException(InvalidFieldConfigurationException::class);
		$this->expectExceptionMessage("Invalid number field config: decimals '-10' must be greater or equal to 0");

		new NumberField(
			label: "label",
			minValue: 1,
			decimals: -10,
			steps: 1,
		);
	}

	/**
	 */
	public function testMaxValueValidationValid () : void
	{
		new NumberField(
			label: "label",
			minValue: 1,
			maxValue: 10,
			steps: 1,
		);

		self::assertTrue(true, "Should not throw");
	}

	/**
	 * fmod() is prone to floating point rounding issues, so this combination
	 * (which is a clean multiple of steps) must not be rejected.
	 */
	public function testMaxValueValidationValidWithFloatingPointStep () : void
	{
		new NumberField(
			label: "label",
			minValue: 0,
			maxValue: 5,
			decimals: 4,
			steps: 0.0001,
		);

		self::assertTrue(true, "Should not throw");
	}

	/**
	 */
	public function testMaxValueValidationInvalidWithFractionalStep () : void
	{
		$this->expectException(InvalidFieldConfigurationException::class);
		$this->expectExceptionMessage("Invalid number field config: the max value '1' should be min value '0' + a multiple of steps '0.3'");

		new NumberField(
			label: "label",
			minValue: 0,
			maxValue: 1,
			steps: 0.3,
		);
	}

	/**
	 */
	public function testZeroStepsInvalid () : void
	{
		$this->expectException(InvalidFieldConfigurationException::class);
		$this->expectExceptionMessage("Invalid number field config: steps '0' must be greater than 0");

		new NumberField(
			label: "label",
			minValue: 0,
			maxValue: 5,
			steps: 0,
		);
	}

	/**
	 */
	public function testNegativeStepsInvalid () : void
	{
		$this->expectException(InvalidFieldConfigurationException::class);
		$this->expectExceptionMessage("Invalid number field config: steps '-1' must be greater than 0");

		new NumberField(
			label: "label",
			minValue: 0,
			maxValue: 5,
			steps: -1,
		);
	}

	/**
	 */
	public function testZeroStepsWithoutMinMaxInvalid () : void
	{
		$this->expectException(InvalidFieldConfigurationException::class);
		$this->expectExceptionMessage("Invalid number field config: steps '0' must be greater than 0");

		new NumberField(
			label: "label",
			steps: 0,
		);
	}

	/**
	 */
	public function testZeroMinValueAndDecimals() : void
	{
		$field = new NumberField(
			label: "label",
			minValue: 0,
			decimals: 0,
		);

		$apiData = new ManagementApiData();
		$field->registerManagementApiData("asset", $apiData);

		$actual = $apiData->getFullConfig()["asset"];

		self::assertArrayHasKey('min_value', $actual);
		self::assertArrayHasKey('decimals', $actual);
		self::assertSame(0, $actual['min_value']);
		self::assertSame(0, $actual['decimals']);
	}

	private static function getApiData (NumberField $field) : array
	{
		$apiData = new ManagementApiData();
		$field->registerManagementApiData("field", $apiData);

		return $apiData->getFullConfig()["field"];
	}

	public function testManagementApiDefaults () : void
	{
		$actual = self::getApiData(new NumberField("My Label"));

		self::assertSame("number", $actual["type"]);
		self::assertSame("My Label", $actual["display_name"]);
		self::assertNull($actual["default_value"]);
		self::assertNull($actual["description"]);
		self::assertFalse($actual["tooltip"]);
		self::assertFalse($actual["translatable"]);
		self::assertFalse($actual["required"]);
		self::assertNull($actual["regex"]);
		// number fields are not exported for translation by default
		self::assertTrue($actual["no_translate"]);
		// numeric constraints are absent when not set
		self::assertArrayNotHasKey("min_value", $actual);
		self::assertArrayNotHasKey("max_value", $actual);
		self::assertArrayNotHasKey("decimals", $actual);
		self::assertArrayNotHasKey("steps", $actual);
	}

	public static function provideManagementApiData () : iterable
	{
		yield "default value" => [
			new NumberField("label", defaultValue: 42),
			["default_value" => "42"],
		];

		yield "export translation" => [
			new NumberField("label", exportTranslation: true),
			["no_translate" => false],
		];

		yield "min value" => [
			new NumberField("label", minValue: 5),
			["min_value" => 5],
		];

		yield "max value" => [
			new NumberField("label", maxValue: 100),
			["max_value" => 100],
		];

		yield "decimals" => [
			new NumberField("label", decimals: 2),
			["decimals" => 2],
		];

		yield "steps" => [
			new NumberField("label", minValue: 0, maxValue: 10, steps: 5),
			["steps" => 5],
		];

		yield "float min value" => [
			new NumberField("label", minValue: 0.5),
			["min_value" => 0.5],
		];
	}

	/**
	 * @param array<string, mixed> $expected
	 */
	#[DataProvider("provideManagementApiData")]
	public function testManagementApiData (NumberField $field, array $expected) : void
	{
		$actual = self::getApiData($field);

		foreach ($expected as $key => $value)
		{
			self::assertSame($value, $actual[$key] ?? null, "Mismatch for key '{$key}'");
		}
	}

	/**
	 */
	public static function provideValidateDataValid () : iterable
	{
		yield "negative value within negative range" => [
			new NumberField("label", minValue: -5, maxValue: 5, decimals: 4),
			"-1.0000",
		];

		yield "min bound within negative range" => [
			new NumberField("label", minValue: -5, maxValue: 5, decimals: 4),
			"-5.0000",
		];

		yield "max bound within negative range" => [
			new NumberField("label", minValue: -5, maxValue: 5, decimals: 4),
			"5.0000",
		];

		yield "negative value without configured range" => [
			new NumberField("label"),
			"-100.5",
		];

		yield "null is valid when not required" => [
			new NumberField("label", minValue: -5, maxValue: 5),
			null,
		];
	}

	/**
	 */
	#[DataProvider("provideValidateDataValid")]
	public function testValidateDataValid (NumberField $field, mixed $data) : void
	{
		$context = self::createComponentContext();
		$field->validateData($context, [], $data, []);
		self::assertTrue(true, "should not throw");
	}

	/**
	 */
	public static function provideValidateDataInvalid () : iterable
	{
		yield "negative value below min" => [
			new NumberField("label", minValue: -5, maxValue: 5),
			"-6.0000",
		];

		yield "value above max" => [
			new NumberField("label", minValue: -5, maxValue: 5),
			"10.0000",
		];

		yield "negative value rejected by legacy positive-only range" => [
			new NumberField("label", minValue: 0, maxValue: 10),
			"-1.0000",
		];

		yield "minus sign followed by whitespace" => [
			new NumberField("label", minValue: -5, maxValue: 5),
			"- 1",
		];

		yield "leading whitespace before minus sign" => [
			new NumberField("label", minValue: -5, maxValue: 5),
			" -1",
		];

		yield "trailing whitespace" => [
			new NumberField("label", minValue: -5, maxValue: 5),
			"-1.0000 ",
		];

		yield "non-numeric string" => [
			new NumberField("label"),
			"abc",
		];

		yield "malformed number" => [
			new NumberField("label"),
			"1.2.3",
		];
	}

	/**
	 */
	#[DataProvider("provideValidateDataInvalid")]
	public function testValidateDataInvalid (NumberField $field, mixed $data) : void
	{
		$this->expectException(InvalidDataException::class);

		$context = self::createComponentContext();
		$field->validateData($context, [], $data, []);
	}

	/**
	 */
	private static function createComponentContext () : ComponentContext
	{
		return new ComponentContext(
			self::createStub(ComponentManager::class),
			new DataTransformer(),
			new NullLogger(),
			new DataValidator(),
			new ImageDimensionsExtractor(),
		);
	}
}
