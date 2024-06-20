<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Image;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Torr\Storyblok\Image\ImageDimensionsExtractor;

/**
 * @internal
 */
final class ImageDimensionsExtractorTest extends TestCase
{
	/**
	 *
	 */
	public static function provideExtraction () : iterable
	{
		yield [
			"https://a.storyblok.com/f/space/1157x1143/hash/filename.png",
			[1157, 1143],
		];

		yield [
			"https://a.storyblok.com/f/space/1x2/hash/filename.png",
			[1, 2],
		];

		yield [
			"https://a.storyblok.com/f/space/1/hash/filename.png",
			[null, null],
		];

		yield [
			"https://a.storyblok.com/",
			[null, null],
		];

		yield [
			"test",
			[null, null],
		];
	}

	/**
	 */
	#[DataProvider("provideExtraction")]
	public function testExtraction (string $url, array $expected) : void
	{
		$extractor = new ImageDimensionsExtractor();
		self::assertSame($expected, $extractor->extractImageDimensions($url));
	}
}
