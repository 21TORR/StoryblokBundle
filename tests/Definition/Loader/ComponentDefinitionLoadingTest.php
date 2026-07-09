<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Definition\Loader;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tests\Torr\Storyblok\Fixtures\Components\Empty\EmptyClass;
use Tests\Torr\Storyblok\Fixtures\Components\FullNestedBlock;
use Tests\Torr\Storyblok\Fixtures\Components\FullBlock;
use Tests\Torr\Storyblok\Fixtures\Components\InvalidBaseDefinition\BlockMissingAttribute;
use Tests\Torr\Storyblok\Fixtures\Components\InvalidBaseDefinition\BlokAndDocumentSet;
use Tests\Torr\Storyblok\Fixtures\Components\InvalidBaseDefinition\MissingBaseClass;
use Tests\Torr\Storyblok\Fixtures\Components\InvalidBaseDefinition\DocumentMissingBaseClass;
use Tests\Torr\Storyblok\Fixtures\Components\InvalidBaseDefinition\NestedMissingAttribute;
use Tests\Torr\Storyblok\Fixtures\Components\InvalidBaseDefinition\StoryMissingAttribute;
use Tests\Torr\Storyblok\Fixtures\Components\Simple;
use Torr\Storyblok\Component\Config\ComponentType;
use Torr\Storyblok\Definition\Data\ComponentDefinition;
use Torr\Storyblok\Definition\DefinitionRegistry;
use Torr\Storyblok\Definition\Exception\InvalidComponentDefinitionException;
use Torr\Storyblok\Definition\Loader\ComponentDefinitionLoader;
use Torr\Storyblok\Definition\Loader\FieldDefinitionLoader;
use Torr\Storyblok\Definition\Mapping\Component;
use Torr\Storyblok\Story\Data\Block;
use Torr\Storyblok\Story\Data\Story;

/**
 * @internal
 */
final class ComponentDefinitionLoadingTest extends TestCase
{
	/**
	 *
	 */
	public static function provideInvalidBaseDefinition () : iterable
	{
		yield "block, missing attribute" => [
			BlockMissingAttribute::class,
			\sprintf(
				"Block component '%s' must have attribute '%s'",
				BlockMissingAttribute::class,
				Component::class,
			),
		];

		yield "document, missing attribute" => [
			StoryMissingAttribute::class,
			\sprintf(
				"Story component '%s' must have attribute '%s'",
				StoryMissingAttribute::class,
				Component::class,
			),
		];

		yield "missing base class" => [
			MissingBaseClass::class,
			\sprintf(
				"Storyblok component '%s' must either extend '%s' or '%s'",
				MissingBaseClass::class,
				Story::class,
				Block::class,
			),
		];
	}

	/**
	 *
	 */
	#[DataProvider("provideInvalidBaseDefinition")]
	public function testInvalidBaseDefinition (string $storyClass, string $expectedMessage) : void
	{
		$this->expectException(InvalidComponentDefinitionException::class);
		$this->expectExceptionMessage($expectedMessage);

		$registry = $this->createRegistry();
		$registry->register($storyClass);
	}

	/**
	 *
	 */
	public function testEmpty () : void
	{
		$registry = $this->createRegistry();
		$registry->register(EmptyClass::class);

		self::assertNull($registry->getByStoryClass(EmptyClass::class));
	}

	/**
	 *
	 */
	public static function provideValidBaseDefinition () : iterable
	{
		yield "simple" => [
			Simple::class,
			new ComponentDefinition(
				storyClass: Simple::class,
				key: "simple",
				label: "Simple Label",
				type: ComponentType::Standalone,
				fields: [],
			),
		];

		yield "full standalone" => [
			FullBlock::class,
			new ComponentDefinition(
				storyClass: FullBlock::class,
				key: "full-standalone-block",
				label: "Full Standalone Block",
				type: ComponentType::Standalone,
				fields: [],
				description: "This is my description",
			),
		];

		yield "full nested" => [
			FullNestedBlock::class,
			new ComponentDefinition(
				storyClass: FullNestedBlock::class,
				key: "full-nested-block",
				label: "Full Nested Block",
				type: ComponentType::Nested,
				fields: [],
			),
		];
	}

	/**
	 *
	 */
	#[DataProvider("provideValidBaseDefinition")]
	public function testValidBaseDefinition (
		string $storyClass,
		ComponentDefinition $expected,
	) : void
	{
		$registry = $this->createRegistry();
		$registry->register($storyClass);
		$actual = $registry->getByStoryClass($storyClass);

		self::assertSame($expected->storyClass, $actual->storyClass);
		self::assertSame($expected->key, $actual->key);
		self::assertSame($expected->label, $actual->label);
		self::assertSame($expected->type, $actual->type);
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
}
