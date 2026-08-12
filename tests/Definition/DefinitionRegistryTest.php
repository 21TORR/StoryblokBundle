<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Definition;

use Tests\Torr\Storyblok\Fixtures\Components\Tags\ComponentWithTagAB;
use Tests\Torr\Storyblok\Fixtures\Components\Tags\ComponentWithTagAC;
use Tests\Torr\Storyblok\Fixtures\Components\Tags\ComponentWithTagBC;
use Tests\Torr\Storyblok\Fixtures\Components\Tags\FixtureComponentTags;
use Torr\Storyblok\Definition\Data\ComponentDefinition;
use Torr\Storyblok\Definition\DefinitionRegistry;
use PHPUnit\Framework\TestCase;
use Torr\Storyblok\Definition\Loader\ComponentDefinitionLoader;
use Torr\Storyblok\Definition\Loader\FieldDefinitionLoader;

class DefinitionRegistryTest extends TestCase
{
	public function testFetchByTag ()
	{
		$registry = new DefinitionRegistry(
			new ComponentDefinitionLoader(new FieldDefinitionLoader())
		);

		$registry->register(ComponentWithTagAB::class);
		$registry->register(ComponentWithTagAC::class);
		$registry->register(ComponentWithTagBC::class);

		self::assertEmpty($registry->getAllWithTag("missing"));

		$withA = $registry->getAllWithTag(FixtureComponentTags::A);
		self::assertCount(2, $withA);
		self::assertSame(
			[
				"with-tag-ab",
				"with-tag-ac",
			],
			\array_map(
				static fn (ComponentDefinition $definition) => $definition->key,
				$withA,
			),
		);
	}
}
