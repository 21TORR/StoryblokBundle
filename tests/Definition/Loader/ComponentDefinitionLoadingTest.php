<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Definition\Loader;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tests\Torr\Storyblok\Fixtures\Components\Empty\EmptyClass;
use Tests\Torr\Storyblok\Fixtures\Components\InvalidBaseDefinition\BlokAndDocumentSet;
use Tests\Torr\Storyblok\Fixtures\Components\InvalidBaseDefinition\BlokMissingBaseClass;
use Tests\Torr\Storyblok\Fixtures\Components\InvalidBaseDefinition\DocumentMissingBaseClass;
use Tests\Torr\Storyblok\Fixtures\Components\InvalidBaseDefinition\NestedMissingAttribute;
use Tests\Torr\Storyblok\Fixtures\Components\InvalidBaseDefinition\StandaloneMissingAttribute;
use Tests\Torr\Storyblok\Fixtures\Components\Simple;
use Torr\Storyblok\Component\Config\ComponentType;
use Torr\Storyblok\Definition\Data\ComponentDefinition;
use Torr\Storyblok\Definition\DefinitionRegistry;
use Torr\Storyblok\Definition\Exception\InvalidComponentDefinitionException;
use Torr\Storyblok\Definition\Loader\ComponentDefinitionLoader;
use Torr\Storyblok\Definition\Loader\FieldDefinitionLoader;
use Torr\Storyblok\Definition\Mapping\NestedBlock;
use Torr\Storyblok\Definition\Mapping\StandaloneBlock;
use Torr\Storyblok\Story\Data\NestedStory;
use Torr\Storyblok\Story\Data\StandaloneStory;

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
		yield "blok, missing attribute" => [
			NestedMissingAttribute::class,
			\sprintf(
				"Blok component '%s' must have attribute '%s'",
				NestedMissingAttribute::class,
				NestedBlock::class,
			),
		];

		yield "blok, missing base class" => [
			BlokMissingBaseClass::class,
			\sprintf(
				"Blok component '%s' must extend '%s'",
				BlokMissingBaseClass::class,
				NestedStory::class,
			),
		];

		yield "document, missing attribute" => [
			StandaloneMissingAttribute::class,
			\sprintf(
				"Document component '%s' must have attribute '%s'",
				StandaloneMissingAttribute::class,
				StandaloneBlock::class,
			),
		];

		yield "document, missing base class" => [
			DocumentMissingBaseClass::class,
			\sprintf(
				"Document component '%s' must extend '%s'",
				DocumentMissingBaseClass::class,
				StandaloneStory::class,
			),
		];

		yield "document and blok set" => [
			BlokAndDocumentSet::class,
			\sprintf(
				"Class '%s' can't be both document and blok. Remove one of the attribute.",
				BlokAndDocumentSet::class,
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

	private function createRegistry () : DefinitionRegistry
	{
		return new DefinitionRegistry(
			new ComponentDefinitionLoader(
				new FieldDefinitionLoader(),
			),
		);
	}
}
