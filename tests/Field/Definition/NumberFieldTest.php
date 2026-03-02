<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Field\Definition;

use PHPUnit\Framework\TestCase;
use Torr\Storyblok\Exception\InvalidFieldConfigurationException;
use Torr\Storyblok\Field\Definition\NumberField;

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
}
