<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Field\Definition;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tests\Torr\Storyblok\Context\ComponentContextTestHelperTrait;
use Torr\Storyblok\Component\AbstractComponent;
use Torr\Storyblok\Component\Filter\ComponentFilter;
use Torr\Storyblok\Exception\Component\UnknownComponentKeyException;
use Torr\Storyblok\Exception\Story\InvalidDataException;
use Torr\Storyblok\Field\Definition\BloksField;
use Torr\Storyblok\Management\ManagementApiData;
use Torr\Storyblok\Manager\ComponentManager;
use Torr\Storyblok\Manager\Sync\Filter\ResolvableComponentFilter;

/**
 * @internal
 */
final class BloksFieldTest extends TestCase
{
	use ComponentContextTestHelperTrait;

	/**
	 *
	 */
	public function testInvalidComponents () : void
	{
		$field = new BloksField("test");
		$manager = self::createStub(ComponentManager::class);

		$context = $this->createDummyContext($manager);

		$field->validateData($context, ["path"], [
			[
				"component" => "_unknown",
			],
		], []);
		self::assertTrue(true, "Should ignore missing components");
	}

	/**
	 *
	 */
	public static function provideIgnoredComponentsCount () : iterable
	{
		yield "too few" => [
			"data" => [
				["component" => "valid"],
				["component" => "valid"],
				["component" => "invalid"],
				["component" => "invalid"],
			],
			"message" => "Found 2 (known) components, but was expecting at least 3",
			"minCount" => 3,
		];

		yield "too many" => [
			"data" => [
				["component" => "valid"],
				["component" => "valid"],
				["component" => "invalid"],
				["component" => "invalid"],
			],
			"message" => "Found 2 (known) components, but was expecting at most 1",
			"minCount" => null,
			"maxCount" => 1,
		];
	}

	/**
	 *
	 */
	#[DataProvider("provideIgnoredComponentsCount")]
	public function testIgnoredComponentsCount (
		array $data,
		string $message,
		?int $minCount = null,
		?int $maxCount = null,
	) : void
	{
		$field = new BloksField(
			"test",
			minimumNumberOfBloks: $minCount,
			maximumNumberOfBloks: $maxCount,
		);
		$manager = self::createStub(ComponentManager::class);

		$manager->method("getComponent")
			->willReturnCallback(function (string $key)
			{
				if ("valid" !== $key)
				{
					throw new UnknownComponentKeyException("test", "key");
				}

				return $this->createStub(AbstractComponent::class);
			});

		$this->expectException(InvalidDataException::class);
		$this->expectExceptionMessage($message);

		$context = $this->createDummyContext($manager);
		$field->validateData($context, ["path"], $data, []);
	}

	// region Management API Data

	private static function getApiData (BloksField $field) : array
	{
		$apiData = new ManagementApiData();
		$field->registerManagementApiData("field", $apiData);

		return $apiData->getFullConfig()["field"];
	}

	public function testManagementApiDefaults () : void
	{
		$actual = self::getApiData(new BloksField("My Label"));

		self::assertSame("bloks", $actual["type"]);
		self::assertSame("My Label", $actual["display_name"]);
		self::assertNull($actual["default_value"]);
		self::assertNull($actual["description"]);
		self::assertFalse($actual["tooltip"]);
		self::assertFalse($actual["translatable"]);
		self::assertFalse($actual["required"]);
		self::assertNull($actual["regex"]);
		self::assertNull($actual["minimum"]);
		self::assertNull($actual["maximum"]);
		self::assertInstanceOf(ResolvableComponentFilter::class, $actual["component_whitelist"]);
	}

	public function testMinimum () : void
	{
		$actual = self::getApiData(new BloksField("label", minimumNumberOfBloks: 1));

		self::assertSame(1, $actual["minimum"]);
	}

	public function testMaximum () : void
	{
		$actual = self::getApiData(new BloksField("label", maximumNumberOfBloks: 5));

		self::assertSame(5, $actual["maximum"]);
	}

	public static function provideAllowedComponentsProducesResolvableFilter () : iterable
	{
		yield "empty filter (unrestricted)" => [
			new ComponentFilter(),
		];

		yield "filtered by component keys" => [
			ComponentFilter::keys("card", "teaser"),
		];

		yield "filtered by tags" => [
			ComponentFilter::tags("nestable"),
		];
	}

	#[DataProvider("provideAllowedComponentsProducesResolvableFilter")]
	public function testAllowedComponentsProducesResolvableFilter (ComponentFilter $filter) : void
	{
		$actual = self::getApiData(new BloksField("label", allowedComponents: $filter));

		self::assertInstanceOf(ResolvableComponentFilter::class, $actual["component_whitelist"]);
	}

	// endregion
}
