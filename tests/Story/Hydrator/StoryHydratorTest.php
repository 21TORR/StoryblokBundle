<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Story\Hydrator;

use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Tests\Torr\Storyblok\Fixtures\Block\RichTextBlock;
use Tests\Torr\Storyblok\Fixtures\Components\Embed\EmbeddedValue;
use Tests\Torr\Storyblok\Fixtures\Components\Embed\EmbedWithNestedEmbed;
use Tests\Torr\Storyblok\Fixtures\Components\Simple;
use Tests\Torr\Storyblok\Fixtures\Components\StandaloneWithNestedEmbed;
use Tests\Torr\Storyblok\Fixtures\Components\WithBlocks;
use Tests\Torr\Storyblok\Fixtures\Components\WithEmbed;
use Torr\Storyblok\Context\ComponentContext;
use Torr\Storyblok\Definition\DefinitionRegistry;
use Torr\Storyblok\Definition\Loader\ComponentDefinitionLoader;
use Torr\Storyblok\Definition\Loader\FieldDefinitionLoader;
use Torr\Storyblok\Story\Hydrator\MetaDataHydrator;
use Torr\Storyblok\Story\Hydrator\StoryHydrator;
use Torr\Storyblok\Story\MetaData\StandaloneStoryMetaData;

/**
 * @internal
 */
final class StoryHydratorTest extends TestCase
{
	/**
	 *
	 */
	public function testHydration () : void
	{
		$hydrator = $this->createHydrator([Simple::class]);

		$story = $hydrator->hydrateDocument([
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
		], "123", 1);

		self::assertInstanceOf(Simple::class, $story);
		self::assertSame("abc", $story->text);
		self::assertInstanceOf(StandaloneStoryMetaData::class, $story->metaData);
	}

	public function testBloksField () : void
	{
		return;
		$hydrator = $this->createHydrator([
			WithBlocks::class,
			RichTextBlock::class,
		]);
		$data = json_decode(
			<<<'JSON'
					{"name":"Test 2","created_at":"2025-03-11T18:03:14.638Z","published_at":"2026-07-06T12:15:06.086Z","updated_at":"2026-07-06T12:15:06.099Z","id":638706691,"uuid":"a7dd19a3-599c-47a2-88f3-296457eaa74d","content":{"_uid":"a9c06856-3d7d-4d2c-9ef3-9af20783b9e2","blocks":[{"cta":[],"_uid":"c33acdbe-6d1b-4303-8941-ee59a3861e80","content":{"type":"doc","content":[{"type":"paragraph","attrs":{"textAlign":null},"content":[{"text":"This is a richtext","type":"text"}]}]},"component":"rich-text-block","background":"none","secondCtaType":"secondary","_editable":"\u003c!--#storyblok#{\"name\": \"rich-text-block\", \"space\": \"264311\", \"uid\": \"c33acdbe-6d1b-4303-8941-ee59a3861e80\", \"id\": \"638706691\"}--\u003e"}],"headline":"Headline","component":"with-blocks","_editable":"\u003c!--#storyblok#{\"name\": \"with-blocks\", \"space\": \"264311\", \"uid\": \"a9c06856-3d7d-4d2c-9ef3-9af20783b9e2\", \"id\": \"638706691\"}--\u003e"},"slug":"test-2","full_slug":"test-2","sort_by_date":null,"position":-20,"tag_list":[],"is_startpage":false,"parent_id":null,"meta_data":null,"group_id":"0ac3b725-088f-4b2b-8d65-a1609a00f0b9","first_published_at":"2025-03-11T18:03:59.588Z","release_id":null,"lang":"default","path":null,"alternates":[],"default_full_slug":null,"translated_slugs":null}
				JSON,
			true,
			512,
			\JSON_THROW_ON_ERROR,
		);

		$story = $hydrator->hydrateDocument($data, "123", 1);

		self::assertInstanceOf(WithBlocks::class, $story);
		self::assertSame("Headline", $story->headline);
		self::assertIsArray($story->blocks);
		self::assertCount(1, $story->blocks);
		self::assertInstanceOf(RichTextBlock::class, $story->blocks[0]);
		self::assertSame("secondary", $story->blocks[0]->cta2);
	}

	public function testEmbed () : void
	{
		$hydrator = $this->createHydrator([
			WithEmbed::class,
		]);
		$data = json_decode(
			<<<'JSON'
					{"name":"With Embed","created_at":"2026-07-06T12:28:05.150Z","published_at":"2026-07-06T12:28:16.628Z","updated_at":"2026-07-06T12:28:16.637Z","id":195179664003534,"uuid":"0f1e4d20-3b3d-4125-9e92-58f08d6863c8","content":{"_uid":"2df6e808-70f2-4bec-a08e-b5b047d99beb","headline":"Headline","component":"with-embed","nested_link":"Nested Link","nested_label":"Nested Label"},"slug":"with-embed","full_slug":"with-embed","sort_by_date":null,"position":-70,"tag_list":[],"is_startpage":false,"parent_id":null,"meta_data":null,"group_id":"08b0375c-e571-4c9b-a499-c8c01a11bd61","first_published_at":"2026-07-06T12:28:16.628Z","release_id":null,"lang":"default","path":null,"alternates":[],"default_full_slug":null,"translated_slugs":null}
				JSON,
			true,
			512,
			\JSON_THROW_ON_ERROR,
		);

		$story = $hydrator->hydrateDocument($data, "123", 1);

		self::assertInstanceOf(WithEmbed::class, $story);
		self::assertSame("Headline", $story->headline);
		self::assertInstanceOf(EmbeddedValue::class, $story->embed);
		self::assertSame("Nested Label", $story->embed->label);
		self::assertSame("Nested Link", $story->embed->link);
	}

	public function testNestedEmbed () : void
	{
		$hydrator = $this->createHydrator([
			StandaloneWithNestedEmbed::class,
		]);
		$data = json_decode(
			<<<'JSON'
					{
						"name": "With NestedEmbed",
						"created_at": "2026-07-06T12:28:05.150Z",
						"published_at": "2026-07-06T12:28:16.628Z",
						"updated_at": "2026-07-06T12:28:16.637Z",
						"id": 195179664003534,
						"uuid": "0f1e4d20-3b3d-4125-9e92-58f08d6863c8",
						"content": {
							"_uid": "2df6e808-70f2-4bec-a08e-b5b047d99beb",
							"headline": "Headline",
							"component": "with-nested-embed",
							"nested_headline": "Nested Headline",
							"nested_inner_link": "Nested Link",
							"nested_inner_label": "Nested Label"
						},
						"slug": "test",
						"full_slug": "test",
						"sort_by_date": null,
						"position": -70,
						"tag_list": [],
						"is_startpage": false,
						"parent_id": null,
						"meta_data": null,
						"group_id": "08b0375c-e571-4c9b-a499-c8c01a11bd61",
						"first_published_at": "2026-07-06T12:28:16.628Z",
						"release_id": null,
						"lang": "default",
						"path": null,
						"alternates": [],
						"default_full_slug": null,
						"translated_slugs": null
					}
				JSON,
			true,
			512,
			\JSON_THROW_ON_ERROR,
		);

		$story = $hydrator->hydrateDocument($data, "123", 1);

		self::assertInstanceOf(StandaloneWithNestedEmbed::class, $story);
		self::assertSame("Headline", $story->headline);
		self::assertInstanceOf(EmbedWithNestedEmbed::class, $story->embed);
		self::assertSame("Nested Headline", $story->embed->headline);
		self::assertInstanceOf(EmbeddedValue::class, $story->embed->embed);
		self::assertSame("Nested Label", $story->embed->embed->label);
		self::assertSame("Nested Link", $story->embed->embed->link);
	}

	/**
	 *
	 */
	private function createHydrator (
		array $components,
	) : StoryHydrator
	{
		$definitionRegistry = new DefinitionRegistry(
			new ComponentDefinitionLoader(new FieldDefinitionLoader()),
		);

		foreach ($components as $component)
		{
			$definitionRegistry->register($component);
		}

		return new StoryHydrator(
			definitionRegistry: $definitionRegistry,
			accessor: PropertyAccess::createPropertyAccessor(),
			componentContext: new ComponentContext(),
			metaDataHydrator: new MetaDataHydrator(),
		);
	}
}
