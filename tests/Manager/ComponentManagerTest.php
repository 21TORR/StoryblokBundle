<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Manager;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Torr\Storyblok\Component\Filter\ComponentFilter;
use Torr\Storyblok\Manager\ComponentManager;

/**
 * @internal
 */
final class ComponentManagerTest extends TestCase
{
	public function testGetComponentKeysForTagsOnlyKey () : void
	{
		$filter = new ComponentFilter(components: ["test"]);

		$componentManager = $this->getMockBuilder(ComponentManager::class)
			->setConstructorArgs([new ServiceLocator([])])
			->onlyMethods(['getComponentKeysForTags'])
			->getMock();

		$componentManager
			->expects(self::never())
			->method('getComponentKeysForTags');

		$components = $componentManager->getComponentKeysForFilter($filter);

		self::assertIsArray($components);
		self::assertSame(["test"], $components);
	}

	public function testGetComponentKeysForTagsKeyAndTag () : void
	{
		$filter = new ComponentFilter(["tag1"], ["test"]);

		$componentManager = $this->getMockBuilder(ComponentManager::class)
			->setConstructorArgs([new ServiceLocator([])])
			->onlyMethods(['getComponentKeysForTags'])
			->getMock();

		$componentManager
			->expects(self::once())
			->method("getComponentKeysForTags")
			->with(["tag1"])
			->willReturn(["transformedTag1", "transformedTag2"]);

		$components = $componentManager->getComponentKeysForFilter($filter);

		self::assertIsArray($components);
		self::assertSame(["test", "transformedTag1", "transformedTag2"], $components);
	}

	public function testGetComponentKeysForTagsEmpty () : void
	{
		$filter = new ComponentFilter(tags: ["tag"]);

		$componentManager = $this->getMockBuilder(ComponentManager::class)
			->setConstructorArgs([new ServiceLocator([])])
			->onlyMethods(['getComponentKeysForTags'])
			->getMock();

		$componentManager->method("getComponentKeysForTags")
			->willReturn([]);

		$components = $componentManager->getComponentKeysForFilter($filter);

		self::assertIsArray($components);
		self::assertSame([], $components);
	}

	public function testGetComponentKeysForTagsNoDuplicates () : void
	{
		$filter = new ComponentFilter(tags: ["tag"], components: ["test"]);

		$componentManager = $this->getMockBuilder(ComponentManager::class)
			->setConstructorArgs([new ServiceLocator([])])
			->onlyMethods(['getComponentKeysForTags'])
			->getMock();

		$componentManager->method("getComponentKeysForTags")
			->willReturn(["test"]);

		$components = $componentManager->getComponentKeysForFilter($filter);

		self::assertIsArray($components);
		self::assertSame(["test"], $components);
	}
}
