<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Story;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Torr\Storyblok\Exception\Story\StoryHydrationFailed;
use Torr\Storyblok\Story\StoryMetaData;

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
			data: [
				"full_slug" => $fullSlug,
				"_locale_level" => $localeLevel,
			],
			type: "test",
			spaceId: "12345",
		);

		self::assertSame($expected, $metaData->getLocaleFromSlug());
	}

	/**
	 */
	public function testCreatedAtInvalidDateThrows () : void
	{
		$metaData = new StoryMetaData(
			data: [
				"full_slug" => "de/test",
				"_locale_level" => 0,
				"created_at" => "not-a-date",
			],
			type: "test",
			spaceId: "12345",
		);

		$this->expectException(StoryHydrationFailed::class);
		$this->expectExceptionMessage("Could not parse date: not-a-date");

		$metaData->getCreatedAt();
	}

	/**
	 */
	public function testGetTranslatedDocumentsMapping () : void
	{
		$metaData = new StoryMetaData(
			data: [
				"full_slug" => "de/root/test",
				"_locale_level" => 0,
				"alternates" => [
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
			],
			type: "test",
			spaceId: "12345",
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
			data: [
				"full_slug" => "root/child/",
				"_locale_level" => 0,
			],
			type: "test",
			spaceId: "12345",
		);

		self::assertSame("root", $metaData->getParentSlug());
	}

	/**
	 */
	public function testPreviewDataExtraction () : void
	{
		$metaData = new StoryMetaData(
			data: [
				"full_slug" => "de/test",
				"_locale_level" => 0,
				"content" => [
					"_editable" => "<!--#storyblok#-->",
				],
			],
			type: "test",
			spaceId: "12345",
		);

		self::assertSame("<!--#storyblok#-->", $metaData->getPreviewData());
	}
}
