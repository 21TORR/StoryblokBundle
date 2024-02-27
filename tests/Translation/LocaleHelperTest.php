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
		// we allow 2 and 3 letter languages
		yield "deu" => ["deu", true];
		yield "deu-de" => ["deu-de", true];
		yield "deu-international" => ["deu-international", true];
		yield "abcd" => ["abcd", false];
		yield "a" => ["a", false];
		yield "a-de" => ["a-de", false];
		yield "de-a" => ["de-a", false];
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
