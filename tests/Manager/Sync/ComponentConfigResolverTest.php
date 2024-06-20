<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Manager\Sync;

use PHPUnit\Framework\TestCase;
use Torr\Storyblok\Component\Filter\ComponentFilter;
use Torr\Storyblok\Manager\ComponentManager;
use Torr\Storyblok\Manager\Sync\ComponentConfigResolver;
use Torr\Storyblok\Manager\Sync\Filter\ResolvableComponentFilter;

/**
 * @internal
 */
final class ComponentConfigResolverTest extends TestCase
{
	/**
	 */
	public function testResolving () : void
	{
		$manager = $this->createMock(ComponentManager::class);
		$resolver = new ComponentConfigResolver($manager);

		$result = $resolver->resolveComponentConfig([
			"test" => 123,
			"ignore" => new ResolvableComponentFilter(new ComponentFilter(["test"]), "filter_tags"),
			"last" => "abc",
		]);

		self::assertCount(3, $result);
		self::assertArrayHasKey("test", $result);
		self::assertArrayHasKey("filter_tags", $result);
		self::assertArrayHasKey("last", $result);
		self::assertArrayNotHasKey("ignore", $result);
	}

	/**
	 */
	public function testResolvingWithEnabled () : void
	{
		$manager = $this->createMock(ComponentManager::class);
		$resolver = new ComponentConfigResolver($manager);

		$result = $resolver->resolveComponentConfig([
			"test" => 123,
			"ignore" => new ResolvableComponentFilter(new ComponentFilter(["test"]), "filter_tags", "filter_enabled"),
			"last" => "abc",
		]);

		self::assertCount(4, $result);
		self::assertArrayHasKey("test", $result);
		self::assertArrayHasKey("filter_tags", $result);
		self::assertArrayHasKey("filter_enabled", $result);
		self::assertArrayHasKey("last", $result);
		self::assertArrayNotHasKey("ignore", $result);
	}
}
