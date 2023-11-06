<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Story;

use Torr\Storyblok\Story\StoryMetaData;
use PHPUnit\Framework\TestCase;

class StoryMetaDataTest extends TestCase
{
	public static function provideValidLocaleLevel () : iterable
	{
		yield "valid: level 0" => [
			0,
			"de/test",
			"de",
		];

		yield "valid: level 1" => [
			1,
			"root/de/test",
			"de",
		];

		yield "invalid: level 0" => [
			0,
			"root/de/test",
			null,
		];

		yield "invalid: level 1" => [
			1,
			"de/test",
			null,
		];
	}

	/**
	 * @dataProvider provideValidLocaleLevel
	 */
	public function testValidLocaleLevel (int $localeLevel, string $fullSlug, ?string $expected) : void
	{
		$metaData = new StoryMetaData([
			"full_slug" => $fullSlug,
			"_locale_level" => $localeLevel,
		], "test");

		self::assertSame($expected, $metaData->getLocaleFromSlug());
	}

}
