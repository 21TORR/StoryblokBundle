<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Release;

use Torr\Storyblok\Release\ReleaseVersion;
use PHPUnit\Framework\TestCase;

class ReleaseVersionTest extends TestCase
{
	/**
	 */
	public static function providePreviewFlag () : iterable
	{
		yield [true, ReleaseVersion::DRAFT];
		yield [false, ReleaseVersion::PUBLISHED];
	}

	/**
	 * @dataProvider providePreviewFlag
	 */
	public function testPreviewFlag (bool $flag, ReleaseVersion $expected) : void
	{
		self::assertSame($expected, ReleaseVersion::createFromPreviewFlag($flag));
	}
}
