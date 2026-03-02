<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Field\Definition;

use PHPUnit\Framework\TestCase;
use Torr\Storyblok\Exception\InvalidFieldConfigurationException;
use Torr\Storyblok\Field\Definition\NumberField;
use Torr\Storyblok\Management\ManagementApiData;

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
}
