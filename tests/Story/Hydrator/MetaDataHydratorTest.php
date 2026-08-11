<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Story\Hydrator;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Torr\Storyblok\Story\Hydrator\MetaDataHydrator;
use Torr\Storyblok\Story\MetaData\BlockMetaData;
use Torr\Storyblok\Story\MetaData\StoryMetaData;

/**
 * @internal
 */
final class MetaDataHydratorTest extends TestCase
{
	/**
	 *
	 */
	public static function provideValidDocumentMetaData () : iterable
	{
		yield "small" => [
			[
				"name" => "Link Test",
				"created_at" => "2026-03-04T20:56:15.030Z",
				"published_at" => "2026-07-02T13:14:29.734Z",
				"updated_at" => "2026-07-02T13:14:29.745Z",
				"id" => 151421644948932,
				"uuid" => "8a2d7e40-0ce0-46e7-9f6c-f90ae5e11993",
				"content" => [
					"_uid" => "4f1ab87c-f8dc-4a08-930e-3ba9c0960e50",
					"component" => "simple",
					"text" => "abc",
				],
				"slug" => "link-test",
				"full_slug" => "link-test",
				"sort_by_date" => null,
				"position" => -40,
				"tag_list" => [],
				"is_startpage" => false,
				"parent_id" => null,
				"meta_data" => null,
				"group_id" => "0848e8fe-edc2-4a35-862f-4722708187e0",
				"first_published_at" => "2026-03-04T20:56:42.178Z",
				"release_id" => null,
				"lang" => "default",
				"path" => null,
				"alternates" => [],
				"default_full_slug" => null,
				"translated_slugs" => null,
			],
			new StoryMetaData(
				uuid: "8a2d7e40-0ce0-46e7-9f6c-f90ae5e11993",
				id: "151421644948932",
				type: "simple",
				name: "Link Test",
				fullSlug: "link-test",
				createdAt: new \DateTimeImmutable("2026-03-04T20:56:15.030Z"),
				spaceId: "123",
				previewData: null,
				firstPublishedAt: new \DateTimeImmutable("2026-03-04T20:56:42.178Z"),
				publishedAt: new \DateTimeImmutable("2026-07-02T13:14:29.734Z"),
				isStartPage: false,
				locale: "default",
				position: -40,
				alternates: [],
				localeLevel: 0,
			),
		];

		yield "draft" => [
			[
				"name" => "Link Test",
				"created_at" => "2026-03-04T20:56:15.030Z",
				"published_at" => "2026-07-02T13:14:29.734Z",
				"updated_at" => "2026-07-02T13:14:29.745Z",
				"id" => 151421644948932,
				"uuid" => "8a2d7e40-0ce0-46e7-9f6c-f90ae5e11993",
				"content" => [
					"_uid" => "4f1ab87c-f8dc-4a08-930e-3ba9c0960e50",
					"component" => "simple",
					"text" => "abc",
					"_editable" => "<!--#storyblok#-->",
				],
				"slug" => "link-test",
				"full_slug" => "link-test",
				"sort_by_date" => null,
				"position" => -40,
				"tag_list" => [],
				"is_startpage" => false,
				"parent_id" => null,
				"meta_data" => null,
				"group_id" => "0848e8fe-edc2-4a35-862f-4722708187e0",
				"first_published_at" => "2026-03-04T20:56:42.178Z",
				"release_id" => null,
				"lang" => "default",
				"path" => null,
				"alternates" => [],
				"default_full_slug" => null,
				"translated_slugs" => null,
			],
			new StoryMetaData(
				uuid: "8a2d7e40-0ce0-46e7-9f6c-f90ae5e11993",
				id: "151421644948932",
				type: "simple",
				name: "Link Test",
				fullSlug: "link-test",
				createdAt: new \DateTimeImmutable("2026-03-04T20:56:15.030Z"),
				spaceId: "123",
				previewData: "<!--#storyblok#-->",
				firstPublishedAt: new \DateTimeImmutable("2026-03-04T20:56:42.178Z"),
				publishedAt: new \DateTimeImmutable("2026-07-02T13:14:29.734Z"),
				isStartPage: false,
				locale: "default",
				position: -40,
				alternates: [],
				localeLevel: 0,
			),
		];

		yield "alternates" => [
			[
				"name" => "Link Test",
				"created_at" => "2026-03-04T20:56:15.030Z",
				"published_at" => "2026-07-02T13:14:29.734Z",
				"updated_at" => "2026-07-02T13:14:29.745Z",
				"id" => 151421644948932,
				"uuid" => "8a2d7e40-0ce0-46e7-9f6c-f90ae5e11993",
				"content" => [
					"_uid" => "4f1ab87c-f8dc-4a08-930e-3ba9c0960e50",
					"component" => "simple",
					"text" => "abc",
				],
				"slug" => "link-test",
				"full_slug" => "link-test",
				"sort_by_date" => null,
				"position" => -40,
				"tag_list" => [],
				"is_startpage" => false,
				"parent_id" => null,
				"meta_data" => null,
				"group_id" => "0848e8fe-edc2-4a35-862f-4722708187e0",
				"first_published_at" => "2026-03-04T20:56:42.178Z",
				"release_id" => null,
				"lang" => "default",
				"path" => null,
				"alternates" => [
					[
						"id" => 460921066,
						"name" => "Startseite",
						"slug" => "website",
						"published" => true,
						"full_slug" => "cs-cz/website/",
						"is_folder" => false,
						"parent_id" => 442218977,
					],
					[
						"id" => 459304499,
						"name" => "Startseite",
						"slug" => "website",
						"published" => true,
						"full_slug" => "sv-se/website/",
						"is_folder" => false,
						"parent_id" => 442222658,
					],
					[
						"id" => 456308375,
						"name" => "Startseite",
						"slug" => "website",
						"published" => true,
						"full_slug" => "fr-fr/website/",
						"is_folder" => false,
						"parent_id" => 442226519,
					],
				],
				"default_full_slug" => null,
				"translated_slugs" => null,
			],
			new StoryMetaData(
				uuid: "8a2d7e40-0ce0-46e7-9f6c-f90ae5e11993",
				id: "151421644948932",
				type: "simple",
				name: "Link Test",
				fullSlug: "link-test",
				createdAt: new \DateTimeImmutable("2026-03-04T20:56:15.030Z"),
				spaceId: "123",
				previewData: null,
				firstPublishedAt: new \DateTimeImmutable("2026-03-04T20:56:42.178Z"),
				publishedAt: new \DateTimeImmutable("2026-07-02T13:14:29.734Z"),
				isStartPage: false,
				locale: "default",
				position: -40,
				alternates: [
					[
						"id" => 460921066,
						"name" => "Startseite",
						"slug" => "website",
						"published" => true,
						"full_slug" => "cs-cz/website/",
						"is_folder" => false,
						"parent_id" => 442218977,
					],
					[
						"id" => 459304499,
						"name" => "Startseite",
						"slug" => "website",
						"published" => true,
						"full_slug" => "sv-se/website/",
						"is_folder" => false,
						"parent_id" => 442222658,
					],
					[
						"id" => 456308375,
						"name" => "Startseite",
						"slug" => "website",
						"published" => true,
						"full_slug" => "fr-fr/website/",
						"is_folder" => false,
						"parent_id" => 442226519,
					],
				],
				localeLevel: 0,
			),
			[
				[
					"id" => 460921066,
					"name" => "Startseite",
					"slug" => "website",
					"published" => true,
					"full_slug" => "cs-cz/website/",
					"is_folder" => false,
					"parent_id" => 442218977,
					'locale' => 'cs-cz',
				],
				[
					"id" => 459304499,
					"name" => "Startseite",
					"slug" => "website",
					"published" => true,
					"full_slug" => "sv-se/website/",
					"is_folder" => false,
					"parent_id" => 442222658,
					'locale' => 'sv-se',
				],
				[
					"id" => 456308375,
					"name" => "Startseite",
					"slug" => "website",
					"published" => true,
					"full_slug" => "fr-fr/website/",
					"is_folder" => false,
					"parent_id" => 442226519,
					'locale' => 'fr-fr',
				],
			],
		];
	}

	/**
	 *
	 */
	#[DataProvider("provideValidDocumentMetaData")]
	public function testValidDocumentMetaData (array $data, StoryMetaData $expected, array $expectedAlternateLanguages = []) : void
	{
		$hydrator = new MetaDataHydrator();
		$metaData = $hydrator->hydrateStandaloneStoryMetaData($data, "123", 0);

		self::assertEquals($expected, $metaData);
		self::assertSame($expectedAlternateLanguages, $metaData->getAlternateLanguages());
	}

	public static function provideValidBlokMetaData () : iterable
	{
		yield "simple" => [
			[
				"cta" => [],
				"_uid" => "c33acdbe-6d1b-4303-8941-ee59a3861e80",
				"component" => "rich-text-block",
				"background" => "none",
				"secondCtaType" => "secondary",
			],
			new BlockMetaData(
				uuid: "c33acdbe-6d1b-4303-8941-ee59a3861e80",
				type: "rich-text-block",
				spaceId: "123",
			),
		];

		yield "draft" => [
			[
				"cta" => [],
				"_uid" => "c33acdbe-6d1b-4303-8941-ee59a3861e80",
				"component" => "rich-text-block",
				"background" => "none",
				"secondCtaType" => "secondary",
				"_editable" => "<--#storyblok-->",
			],
			new BlockMetaData(
				uuid: "c33acdbe-6d1b-4303-8941-ee59a3861e80",
				type: "rich-text-block",
				spaceId: "123",
				previewData: "<--#storyblok-->",
			),
		];
	}

	/**
	 *
	 */
	#[DataProvider("provideValidBlokMetaData")]
	public function testValidBlokMetaData (
		array $data,
		BlockMetaData $expected,
	) : void
	{
		$hydrator = new MetaDataHydrator();
		$metaData = $hydrator->hydrateNestedStoryMetaData($data, "123");

		self::assertEquals($expected, $metaData);
	}
}
