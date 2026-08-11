<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Story;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Torr\Storyblok\Exception\Story\StoryHydrationFailed;
use Torr\Storyblok\Story\MetaData\StoryMetaData;
use function Symfony\Component\Clock\now;

/**
 * @internal
 */
final class StoryMetaDataTest extends TestCase
{
	/**
	 *
	 */
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
	 *
	 */
	#[DataProvider("provideValidLocaleLevel")]
	public function testValidLocaleLevel (int $localeLevel, string $fullSlug, ?string $expected) : void
	{
		$metaData = new StoryMetaData(
			uuid: "uuid",
			id: "id",
			type: "test",
			name: "test",
			fullSlug: $fullSlug,
			createdAt: now(),
			spaceId: "123",
			localeLevel: $localeLevel,
		);

		self::assertSame($expected, $metaData->getLocaleFromSlug());
	}


	/**
	 */
	public function testGetTranslatedDocumentsMapping () : void
	{
		$metaData = new StoryMetaData(
			uuid: "uuid",
			id: "id",
			type: "test",
			name: "test",
			fullSlug: "de/root/test",
			createdAt: now(),
			spaceId: "123",
			alternates: [
				[
					"id" => 1,
					"name" => "English",
					"slug" => "root/test",
					"published" => true,
					"full_slug" => "en/root/test/",
					"is_folder" => false,
					"parent_id" => 10,
				],
				[
					"id" => 2,
					"name" => "French Folder",
					"slug" => "root/test",
					"published" => true,
					"full_slug" => "fr/root/test/",
					"is_folder" => true,
					"parent_id" => 10,
				],
				[
					"id" => 3,
					"name" => "Invalid Locale",
					"slug" => "root/test",
					"published" => true,
					"full_slug" => "invalid-locale/root/test/",
					"is_folder" => false,
					"parent_id" => 10,
				],
			],
		);

		self::assertSame([
			"en" => "en/root/test",
			"fr" => "fr/root/test/",
		], $metaData->getTranslatedDocumentsMapping());
	}

	/**
	 */
	public function testParentSlugWithoutTrailingSlash () : void
	{
		$metaData = new StoryMetaData(
			uuid: "uuid",
			id: "id",
			type: "test",
			name: "test",
			fullSlug: "root/child/",
			createdAt: now(),
			spaceId: "123",
		);

		self::assertSame("root", $metaData->getParentSlug());
	}
}
