<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Manager\Sync\Filter;

use PHPUnit\Framework\TestCase;
use Torr\Storyblok\Definition\Filter\ComponentFilter;
use Torr\Storyblok\Manager\ComponentManager;
use Torr\Storyblok\Manager\Sync\Filter\ResolvableComponentFilter;

/**
 * @internal
 */
final class ResolvableComponentFilterTest extends TestCase
{
	/**
	 */
	public function testResolving () : void
	{
		$filter = new ComponentFilter(components: ["test"]);
		$resolvable = new ResolvableComponentFilter($filter, "field", "enabled");

		$manager = $this->createMock(ComponentManager::class);
		$manager
			->expects(self::once())
			->method("getComponentKeysForFilter")
			->with($filter)
			->willReturn(["test"]);

		$resolved = $resolvable->transformToManagementApiData($manager);

		self::assertIsArray($resolved);
		self::assertCount(2, $resolved);
		self::assertSame(["test"], $resolved["field"]);
		self::assertTrue($resolved["enabled"]);
	}

	/**
	 */
	public function testResolvingWithoutEnabled () : void
	{
		$filter = new ComponentFilter(components: ["test"]);
		$resolvable = new ResolvableComponentFilter($filter, "field");

		$manager = $this->createMock(ComponentManager::class);
		$manager
			->expects(self::once())
			->method("getComponentKeysForFilter")
			->with($filter)
			->willReturn(["test"]);

		$resolved = $resolvable->transformToManagementApiData($manager);

		self::assertIsArray($resolved);
		self::assertCount(1, $resolved);
		self::assertSame(["test"], $resolved["field"]);
	}

	/**
	 */
	public function testResolvingEmpty () : void
	{
		$filter = new ComponentFilter(tags: ["tag"]);
		$resolvable = new ResolvableComponentFilter($filter, "field");

		$manager = $this->createMock(ComponentManager::class);
		$manager
			->expects(self::once())
			->method("getComponentKeysForFilter")
			->with($filter)
			->willReturn([]);

		$resolved = $resolvable->transformToManagementApiData($manager);

		self::assertIsArray($resolved);
		self::assertCount(1, $resolved);
		self::assertSame([], $resolved["field"]);
	}
}
