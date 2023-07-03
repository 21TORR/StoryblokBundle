<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Translation;

use Torr\Storyblok\Translation\LocaleHelper;
use PHPUnit\Framework\TestCase;

class LocaleHelperTest extends TestCase
{
	/**
	 */
	public static function provideLocales () : iterable
	{
		yield "de" => ["de", true];
		yield "de-de" => ["de-de", true];
		yield "de-DE" => ["de-DE", true];
		yield "en" => ["en", true];
		// we are not bound to 2-char locales
		yield "abc" => ["abc", true];
		yield "abc-dev" => ["abc-dev", true];
		yield "ab-ab-ab" => ["ab-ab-ab", false];
		yield "(empty)" => ["", false];
	}


	/**
	 * @dataProvider provideLocales
	 */
	public function testLocales (string $value, bool $expectedValid) : void
	{
		self::assertSame($expectedValid, LocaleHelper::isValidLocale($value));
	}
}
