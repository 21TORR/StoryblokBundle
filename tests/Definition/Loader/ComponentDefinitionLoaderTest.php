<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Definition\Loader;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Torr\Storyblok\Fixtures\Components\Empty\EmptyClass;
use Tests\Torr\Storyblok\Fixtures\Components\InvalidBaseDefinition\BlokAndDocumentSet;
use Tests\Torr\Storyblok\Fixtures\Components\InvalidBaseDefinition\BlokMissingAttribute;
use Tests\Torr\Storyblok\Fixtures\Components\InvalidBaseDefinition\BlokMissingBaseClass;
use Tests\Torr\Storyblok\Fixtures\Components\InvalidBaseDefinition\DocumentMissingAttribute;
use Tests\Torr\Storyblok\Fixtures\Components\InvalidBaseDefinition\DocumentMissingBaseClass;
use Torr\Storyblok\Attribute\AttributeLoader;
use Torr\Storyblok\Definition\Exception\InvalidComponentDefinitionException;
use Torr\Storyblok\Definition\Loader\ComponentDefinitionLoader;
use PHPUnit\Framework\TestCase;
use Torr\Storyblok\Definition\Mapping\Blok;
use Torr\Storyblok\Definition\Mapping\Document;
use Torr\Storyblok\Story\Data\BlokStory;
use Torr\Storyblok\Story\Data\DocumentStory;

class ComponentDefinitionLoaderTest extends TestCase
{
	/**
	 *
	 */
	public static function provideInvalidBaseDefinition () : iterable
	{
		yield "blok, missing attribute" => [
			BlokMissingAttribute::class,
			\sprintf(
				"Blok component '%s' must have attribute '%s'",
				BlokMissingAttribute::class,
				Blok::class,
			),
		];

		yield "blok, missing base class" => [
			BlokMissingBaseClass::class,
			\sprintf(
				"Blok component '%s' must extend '%s'",
				BlokMissingBaseClass::class,
				BlokStory::class,
			),
		];

		yield "document, missing attribute" => [
			DocumentMissingAttribute::class,
			\sprintf(
				"Document component '%s' must have attribute '%s'",
				DocumentMissingAttribute::class,
				Document::class,
			),
		];

		yield "document, missing base class" => [
			DocumentMissingBaseClass::class,
			\sprintf(
				"Document component '%s' must extend '%s'",
				DocumentMissingBaseClass::class,
				DocumentStory::class,
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

		$attributesHelper = new AttributeLoader();
		$loader = new ComponentDefinitionLoader($attributesHelper);

		$loader->loadDefinition($storyClass);
	}


	/**
	 *
	 */
	public function testEmpty () : void
	{
		$attributesHelper = new AttributeLoader();
		$loader = new ComponentDefinitionLoader($attributesHelper);

		self::assertNull($loader->loadDefinition(EmptyClass::class));
	}
}
