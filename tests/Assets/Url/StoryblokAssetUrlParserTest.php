<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Assets\Url;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Torr\Storyblok\Assets\Url\Data\ParsedStoryblokAssetUrl;
use Torr\Storyblok\Assets\Url\StoryblokAssetUrlParser;

/**
 * @internal
 */
final class StoryblokAssetUrlParserTest extends TestCase
{
	/**
	 */
	#[DataProvider("provideParse")]
	public function testParse (
		string $url,
		?array $expected,
	) : void
	{
		$parser = new StoryblokAssetUrlParser();
		$parsed = $parser->parse($url);

		if (null === $expected)
		{
			self::assertNull($parsed);

			return;
		}

		self::assertInstanceOf(ParsedStoryblokAssetUrl::class, $parsed);
		self::assertSame($expected["spaceId"], $parsed->spaceId);
		self::assertSame($expected["path"], $parsed->path);
	}

	/**
	 */
	public static function provideParse () : iterable
	{
		yield "valid simple" => [
			"https://a.storyblok.com/f/123/uploads/image.jpg",
			[
				"spaceId" => "123",
				"path" => "uploads/image.jpg",
			],
		];

		yield "valid nested path" => [
			"https://a.storyblok.com/f/42/folder/sub/image.png",
			[
				"spaceId" => "42",
				"path" => "folder/sub/image.png",
			],
		];

		yield "invalid domain" => [
			"https://example.com/f/123/uploads/image.jpg",
			null,
		];

		yield "invalid protocol" => [
			"http://a.storyblok.com/f/123/uploads/image.jpg",
			null,
		];

		yield "invalid space id" => [
			"https://a.storyblok.com/f/abc/uploads/image.jpg",
			null,
		];

		yield "empty path" => [
			"https://a.storyblok.com/f/123/",
			null,
		];
	}
}
