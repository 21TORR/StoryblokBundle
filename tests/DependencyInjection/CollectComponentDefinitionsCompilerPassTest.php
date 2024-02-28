<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Tests\Torr\Storyblok\Fixtures\Components\ComponentAbc;
use Tests\Torr\Storyblok\Fixtures\Components\ComponentTest1;
use Tests\Torr\Storyblok\Fixtures\Components\ComponentTest2;
use Torr\Storyblok\DependencyInjection\CollectComponentDefinitionsCompilerPass;
use PHPUnit\Framework\TestCase;
use Torr\Storyblok\Exception\Definition\DuplicateComponentKeyException;
use Torr\Storyblok\Manager\ComponentManager;

class CollectComponentDefinitionsCompilerPassTest extends TestCase
{
	/**
	 *
	 */
	public function testDuplicateCollect () : void
	{
		$containerBuilder = new ContainerBuilder();
		$containerBuilder->addDefinitions([
			ComponentTest1::class => new Definition(ComponentTest1::class),
			ComponentTest2::class => new Definition(ComponentTest2::class),
		]);

		$this->expectException(DuplicateComponentKeyException::class);
		$compilerPass = new CollectComponentDefinitionsCompilerPass();
		$compilerPass->process($containerBuilder);
	}


	/**
	 *
	 */
	public function testCollect () : void
	{
		$containerBuilder = new ContainerBuilder();
		$containerBuilder->addDefinitions([
			ComponentTest1::class => new Definition(ComponentTest1::class),
			ComponentAbc::class => new Definition(ComponentAbc::class),
			ComponentManager::class => new Definition(ComponentManager::class),
		]);

		$definitionsInContainerBefore = \array_keys($containerBuilder->getDefinitions());
		self::assertContains(ComponentTest1::class, $definitionsInContainerBefore);
		self::assertContains(ComponentAbc::class, $definitionsInContainerBefore);
		self::assertContains(ComponentManager::class, $definitionsInContainerBefore);

		$compilerPass = new CollectComponentDefinitionsCompilerPass();
		$compilerPass->process($containerBuilder);

		$definition = $containerBuilder->getDefinition(ComponentManager::class);
		$actual = $definition->getArgument('$classesWithDefinitions');

		self::assertEqualsCanonicalizing([
			"test" => ComponentTest1::class,
			"abc" => ComponentAbc::class,
		], $actual);

		$definitionsInContainerAfter = \array_keys($containerBuilder->getDefinitions());
		self::assertNotContains(ComponentTest1::class, $definitionsInContainerAfter);
		self::assertNotContains(ComponentAbc::class, $definitionsInContainerAfter);
		self::assertContains(ComponentManager::class, $definitionsInContainerAfter);
	}
}
