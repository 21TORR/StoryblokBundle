<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Field\Definition;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Torr\Storyblok\Context\ComponentContext;
use Torr\Storyblok\Field\Data\AssetData;
use Torr\Storyblok\Field\Definition\AssetField;
use Torr\Storyblok\Image\ImageDimensionsExtractor;
use Torr\Storyblok\Manager\ComponentManager;
use Torr\Storyblok\Transformer\DataTransformer;
use Torr\Storyblok\Validator\DataValidator;

/**
 * @internal
 */
final class AssetFieldTest extends TestCase
{
	/**
	 *
	 */
	public static function provideTransformInvalidImage () : iterable
	{
		yield "null" => [
			null,
			null,
		];

		yield "empty array" => [
			[],
			null,
		];

		yield "empty filename" => [
			[
				"id" => null,
				"alt" => null,
				"name" => "",
				"focus" => null,
				"title" => null,
				"source" => null,
				"filename" => "",
				"copyright" => null,
				"fieldtype" => "asset",
				"meta_data" => [],
			],
			null,
		];
	}

	/**
	 */
	#[DataProvider("provideTransformInvalidImage")]
	public function testTransformInvalidImage (mixed $data, mixed $expected) : void
	{
		$field = new AssetField("Test");

		$result = $field->transformData(
			$data,
			self::createStub(ComponentContext::class),
			[],
		);

		self::assertSame($expected, $result);
	}

	/**
	 */
	public function testTransformValidImage () : void
	{
		$field = new AssetField("Test");
		$context = new ComponentContext(
			self::createStub(ComponentManager::class),
			new DataTransformer(),
			new NullLogger(),
			new DataValidator(),
			new ImageDimensionsExtractor(),
		);

		$result = $field->transformData(
			[
				"id" => null,
				"alt" => "alt",
				"name" => "name",
				"focus" => "focus",
				"title" => "title",
				"source" => "source",
				"filename" => "https://21torr.com/path/to/1024x768/example.png",
				"copyright" => "copyright",
				"fieldtype" => "asset",
				"meta_data" => [],
				"is_external_url" => false,
			],
			$context,
			[],
		);

		self::assertInstanceOf(AssetData::class, $result);
		self::assertSame("https://21torr.com/path/to/1024x768/example.png", $result->url);
		self::assertSame(1024, $result->width);
		self::assertSame(768, $result->height);
		self::assertSame("alt", $result->alt);
		self::assertSame("name", $result->name);
		self::assertSame("focus", $result->focus);
		self::assertSame("title", $result->title);
		self::assertSame("source", $result->source);
		self::assertSame("copyright", $result->copyright);
		self::assertFalse($result->isExternal);
	}
}
