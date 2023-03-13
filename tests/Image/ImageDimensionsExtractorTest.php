<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Image;

use Torr\Storyblok\Image\ImageDimensionsExtractor;
use PHPUnit\Framework\TestCase;

class ImageDimensionsExtractorTest extends TestCase
{
	/**
	 *
	 */
	public function provideExtraction () : iterable
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
	 * @dataProvider provideExtraction
	 */
	public function testExtraction (string $url, array $expected) : void
	{
		$extractor = new ImageDimensionsExtractor();
		self::assertEquals($expected, $extractor->extractImageDimensions($url));
	}
}
