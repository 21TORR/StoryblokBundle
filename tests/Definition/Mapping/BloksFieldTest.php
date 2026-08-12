<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Definition\Mapping;

use PHPUnit\Framework\Attributes\DataProvider;
use Torr\Storyblok\Definition\Exception\InvalidComponentDefinitionException;
use Torr\Storyblok\Definition\Filter\ComponentFilter;
use Torr\Storyblok\Definition\Mapping\BloksField;
use PHPUnit\Framework\TestCase;

class BloksFieldTest extends TestCase
{
	/**
	 *
	 */
	public static function provideValidCounts () : iterable
	{
		yield "default" => [
			0, null,
		];

		yield "only-min" => [
			10, null,
		];

		yield "both-min-default" => [
			0, 10,
		];

		yield "both-set" => [
			8, 10,
		];
	}


	/**
	 *
	 */
	#[DataProvider("provideValidCounts")]
	public function testValidCounts (
		int $minimumNumber,
		?int $maximumNumber,
	)
	{
		$field = new BloksField(
			"test",
			allow: ComponentFilter::tags("test"),
			minimumNumberOfBloks: $minimumNumber,
			maximumNumberOfBloks: $maximumNumber,
		);

		$managementData = $field->createManagementApiData();

		self::assertSame($minimumNumber, $managementData["minimum"]);
		self::assertSame($maximumNumber, $managementData["maximum"]);
	}

	/**
	 */
	public function testInvalidCounts () : void
	{
		$this->expectException(InvalidComponentDefinitionException::class);

		new BloksField(
			"test",
			allow: ComponentFilter::tags("test"),
			minimumNumberOfBloks: 2,
			maximumNumberOfBloks: 1,
		);
	}
}
