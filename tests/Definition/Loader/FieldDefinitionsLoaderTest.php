<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Definition\Loader;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tests\Torr\Storyblok\Fixtures\Components\FieldOrder\FieldSortOrder;
use Tests\Torr\Storyblok\Fixtures\Components\InvalidFields\DuplicateField;
use Tests\Torr\Storyblok\Fixtures\Field\ComponentWithTextField;
use Torr\Storyblok\Definition\Registry\DefinitionRegistry;
use Torr\Storyblok\Definition\Exception\InvalidFieldDefinitionException;
use Torr\Storyblok\Definition\Loader\ComponentDefinitionLoader;
use Torr\Storyblok\Definition\Loader\FieldDefinitionLoader;

/**
 * @final
 *
 * @internal
 */
class FieldDefinitionsLoaderTest extends TestCase
{
	public function testText () : void
	{
		$loader = new ComponentDefinitionLoader(new FieldDefinitionLoader());

		$component = $loader->loadDefinition(self::createMock(DefinitionRegistry::class), ComponentWithTextField::class);
	}

	/**
	 *
	 */
	public static function provideInvalidDefinitions () : iterable
	{
		yield "duplicate fields" => [
			DuplicateField::class,
			\sprintf(
				"Property '%s::\$text' can't have multiple field attributes",
				DuplicateField::class,
			),
		];
	}

	/**
	 *
	 */
	#[DataProvider("provideInvalidDefinitions")]
	public function testInvalidDefinitions (string $storyClass, string $expectedException) : void
	{
		$this->expectException(InvalidFieldDefinitionException::class);
		$this->expectExceptionMessage($expectedException);

		$registry = $this->createRegistry()
			->register($storyClass);
	}

	public function testSortOrder () : void
	{
		$registry = $this->createRegistry()
			->register(FieldSortOrder::class);

		$definition = $registry->getByStoryClass(FieldSortOrder::class);
		dump($definition->fields);
		static::assertCount(4, $definition->fields);
		static::assertSame("first", $definition->fields[0]->key);
		static::assertSame("nested_label", $definition->fields[1]->key);
		static::assertSame("nested_link", $definition->fields[2]->key);
		static::assertSame("last", $definition->fields[3]->key);
	}

	/**
	 */
	private function createRegistry () : DefinitionRegistry
	{
		return new DefinitionRegistry(
			new ComponentDefinitionLoader(
				new FieldDefinitionLoader(),
			),
		);
	}
}
