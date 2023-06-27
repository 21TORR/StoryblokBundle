<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Field\Definition;

use Tests\Torr\Storyblok\Context\ComponentContextTestHelperTrait;
use Torr\Storyblok\Component\AbstractComponent;
use Torr\Storyblok\Exception\Component\UnknownComponentKeyException;
use Torr\Storyblok\Exception\Story\InvalidDataException;
use Torr\Storyblok\Field\Definition\BloksField;
use PHPUnit\Framework\TestCase;
use Torr\Storyblok\Manager\ComponentManager;

class BloksFieldTest extends TestCase
{
	use ComponentContextTestHelperTrait;

	/**
	 *
	 */
	public function testInvalidComponents () : void
	{
		$field = new BloksField("test");
		$manager = $this->createMock(ComponentManager::class);

		$context = $this->createDummyContext($manager);

		$field->validateData($context, ["path"], [
			[
				"component" => "_unknown",
			]
		], []);
		self::assertTrue(true, "Should ignore missing components");
	}

	public static function provideIgnoredComponentsCount () : iterable
	{
		yield "too few" => [
			"data" => [
				["component" => "valid"],
				["component" => "valid"],
				["component" => "invalid"],
				["component" => "invalid"],
			],
			"error" => "Found 2 (known) components, but was expecting at least 3",
			"minCount" => 3,
		];

		yield "too many" => [
			"data" => [
				["component" => "valid"],
				["component" => "valid"],
				["component" => "invalid"],
				["component" => "invalid"],
			],
			"error" => "Found 2 (known) components, but was expecting at most 1",
			"minCount" => null,
			"maxCount" => 1,
		];
	}


	/**
	 * @dataProvider provideIgnoredComponentsCount
	 */
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
		$manager = $this->createMock(ComponentManager::class);

		$manager->method("getComponent")
			->willReturnCallback(function (string $key)
			{
				if ("valid" !== $key)
				{
					throw new UnknownComponentKeyException();
				}

				return $this->createMock(AbstractComponent::class);
			});

		$this->expectException(InvalidDataException::class);
		$this->expectExceptionMessage($message);

		$context = $this->createDummyContext($manager);
		$field->validateData($context, ["path"], $data, []);
	}
}
