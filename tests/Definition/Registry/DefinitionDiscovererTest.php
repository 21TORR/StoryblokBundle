<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Definition\Registry;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tests\Torr\Storyblok\Fixtures\Components\Simple;
use Tests\Torr\Storyblok\Fixtures\Components\SimpleTranslatable;
use Torr\Storyblok\Definition\Data\ComponentDefinition;
use Torr\Storyblok\Definition\Exception\UnknownComponentException;
use Torr\Storyblok\Definition\Registry\DefinitionDiscoverer;
use Torr\Storyblok\Definition\Registry\DefinitionRegistry;

class DefinitionDiscovererTest extends TestCase
{
	/**
	 *
	 */
	public static function provideDiscovery () : iterable
	{
		yield [
			[
				Simple::class,
				SimpleTranslatable::class,
			],
			[
				Simple::class,
			],
			[
				"simple",
			]
		];

		yield [
			[
				Simple::class,
				SimpleTranslatable::class,
			],
			[
				Simple::class,
			],
			[
				"simple",
			]
		];
	}

	/**
	 *
	 */
	#[DataProvider("provideDiscovery")]
	public function testDiscovery (
		array $loadClasses,
		array $topLevelClasses,
		array $expectedDiscoveredComponents,
	) : void
	{
		$registry = new DefinitionRegistry();
		$discoverer = new DefinitionDiscoverer($registry);

		foreach ($loadClasses as $storyClass)
		{
			$registry->register($storyClass);
		}

		$actualComponentKeys = \array_map(
			static fn (ComponentDefinition $definition) => $definition->key,
			$discoverer->discoverReachableComponents($topLevelClasses),
		);

		self::assertSame($expectedDiscoveredComponents, $actualComponentKeys);
	}

	/**
	 */
	public function testMissing ()
	{
		$this->expectException(UnknownComponentException::class);

		$registry = new DefinitionRegistry();
		$discoverer = new DefinitionDiscoverer($registry);

		$discoverer->discoverReachableComponents([
			"Missing\\Class",
		]);
	}
}
