<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Manager;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Tests\Torr\Storyblok\Fixtures\ComponentA;
use Tests\Torr\Storyblok\Fixtures\ComponentB;
use Tests\Torr\Storyblok\Fixtures\ComponentC;
use Tests\Torr\Storyblok\Fixtures\ComponentD;
use Torr\Storyblok\Definition\Filter\ComponentFilter;
use Torr\Storyblok\Manager\ComponentManager;

/**
 * @internal
 */
final class ComponentManagerTest extends TestCase
{
	/**
	 */
	public function testGetComponentKeysForTagsOnlyKey () : void
	{
		$componentManager = new ComponentManager(new ServiceLocator([
			"a" => static fn () => new ComponentA(["tag1", "tag2"]),
			"b" => static fn () => new ComponentB(["tag2", "tag3"]),
		]));

		$components = $componentManager->getComponentKeysForFilter(
			new ComponentFilter(keys: ["a"]),
		);

		self::assertIsArray($components);
		self::assertSame(["a"], $components);
	}

	/**
	 */
	public function testGetComponentKeysForTagsKeyAndTag () : void
	{
		$filter = new ComponentFilter(["tag3"], ["a"]);

		$componentManager = new ComponentManager(new ServiceLocator([
			"a" => static fn () => new ComponentA(["tag1", "tag2"]),
			"b" => static fn () => new ComponentB(["tag2", "tag3"]),
			"c" => static fn () => new ComponentC(["tag3"]),
			"d" => static fn () => new ComponentD(["tag4"]),
		]));

		$components = $componentManager->getComponentKeysForFilter($filter);

		self::assertIsArray($components);
		self::assertSame(["a", "b", "c"], $components);
	}

	/**
	 */
	public function testGetComponentKeysForTagsEmpty () : void
	{
		$filter = new ComponentFilter(tags: ["tag"]);

		$componentManager = new ComponentManager(new ServiceLocator([]));
		$components = $componentManager->getComponentKeysForFilter($filter);

		self::assertIsArray($components);
		self::assertSame([], $components);
	}

	/**
	 */
	public function testGetComponentKeysForTagsNoDuplicates () : void
	{
		$filter = new ComponentFilter(tags: ["tag1"], keys: ["a"]);

		$componentManager = new ComponentManager(new ServiceLocator([
			"a" => static fn () => new ComponentA(["tag1", "tag2"]),
			"b" => static fn () => new ComponentB(["tag2", "tag3"]),
			"c" => static fn () => new ComponentC(["tag3"]),
			"d" => static fn () => new ComponentD(["tag4"]),
		]));

		$components = $componentManager->getComponentKeysForFilter($filter);

		self::assertIsArray($components);
		self::assertSame(["a"], $components);
	}
}
