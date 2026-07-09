<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Management;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tests\Torr\Storyblok\Fixtures\Components\FieldOrder\FieldSortOrder;
use Tests\Torr\Storyblok\Fixtures\Components\Simple;
use Torr\Storyblok\Definition\DefinitionRegistry;
use Torr\Storyblok\Definition\Loader\ComponentDefinitionLoader;
use Torr\Storyblok\Definition\Loader\FieldDefinitionLoader;
use Torr\Storyblok\Management\ManagementApiGenerator;

class ManagementApiGeneratorTest extends TestCase
{
	/**
	 *
	 */
	public static function provideManagementApiGeneration () : iterable
	{
		yield "simple" => [
			Simple::class,
			[
				"name" => "simple",
				"display_name" => "Simple Label",
				"description" => null,
				"is_root" => true,
				"is_nestable" => false,
				"schema" => [
					"text" => [
						"type" => "text",
						"display_name" => "Text",
						"default_value" => null,
						"rtl" => false,
						"pos" => 0,
						"max_length" => null,
						"no_translate" => false,
					],
				],
			],
		];
	}

	/**
	 *
	 */
	#[DataProvider("provideManagementApiGeneration")]
	public function testManagementApiGeneration (
		string $componentClass,
		array $expected,
	) : void
	{
		$registry = $this->createRegistry();
		$registry->register($componentClass);

		$definition = $registry->getByStoryClass($componentClass);
		self::assertNotNull($definition);

		$apiGenerator = new ManagementApiGenerator($registry);
		$actual = $apiGenerator->generateManagementApiPayload($definition);

		self::assertEquals($expected, $actual);
	}

	/**
	 *
	 */
	private function createRegistry () : DefinitionRegistry
	{
		return new DefinitionRegistry(
			new ComponentDefinitionLoader(
				new FieldDefinitionLoader(),
			),
		);
	}

	/**
	 *
	 */
	public static function provideFieldSortOrder () : iterable
	{
		yield "single embeds" => [
			FieldSortOrder::class,
			[
				"outer1",
				"indirect_first",
				"indirect_inner_a",
				"indirect_inner_b",
				"indirect_second",
				"outer2",
				"direct_a",
				"direct_b",
				"outer3",
				"group_first",
				"group_inner_a",
				"group_inner_b",
				"group_second",
				"group", // the group itself
				"outer4",
			],
		];
	}


	/**
	 *
	 */
	#[DataProvider("provideFieldSortOrder")]
	public function testFieldSortOrder (
		string $componentClass,
		array $expectedFieldsOrder,
	)
	{
		$registry = $this->createRegistry();
		$registry->register($componentClass);

		$definition = $registry->getByStoryClass($componentClass);
		self::assertNotNull($definition);

		$apiGenerator = new ManagementApiGenerator($registry);
		$actual = $apiGenerator->generateManagementApiPayload($definition);

		self::assertSame($expectedFieldsOrder, \array_keys($actual["schema"]));
	}
}
