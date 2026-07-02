<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Api;

use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Torr\Storyblok\Api\ContentApi;
use Torr\Storyblok\Component\AbstractComponent;
use Torr\Storyblok\Component\Config\ComponentType;
use Torr\Storyblok\Config\StoryblokConfig;
use Torr\Storyblok\Context\ComponentContext;
use Torr\Storyblok\Exception\Api\ContentRequestFailedException;
use Torr\Storyblok\Exception\Config\InvalidConfigException;
use Torr\Storyblok\Exception\Config\MissingConfigException;
use Torr\Storyblok\Exception\Story\InvalidDataException;
use Torr\Storyblok\Field\Definition\TextField;
use Torr\Storyblok\Image\ImageDimensionsExtractor;
use Torr\Storyblok\Manager\ComponentManager;
use Torr\Storyblok\Story\Story;
use Torr\Storyblok\Story\StoryFactory;
use Torr\Storyblok\Transformer\DataTransformer;
use Torr\Storyblok\Validator\DataValidator;

/**
 * @internal
 */
final class ContentApiTest extends TestCase
{
	/**
	 */
	public function testFetchSingleStoryReturnsNullOnNotFound () : void
	{
		$api = $this->createApi(
			new MockHttpClient(
				[
					new MockResponse(\json_encode([
						"space" => [
							"id" => "12345",
							"name" => "Test",
							"version" => 123,
							"language_codes" => ["de"],
							"domain" => "example.test",
						],
					])),
					new MockResponse("", [
						"http_code" => 404,
					]),
				],
			),
		);

		self::assertNull($api->fetchSingleStory("missing-slug"));
	}

	/**
	 */
	public function testFetchAllStoriesPaginates () : void
	{
		$responses = [
			new MockResponse((string) json_encode([
				"space" => [
					"id" => 12345,
					"name" => "Test Space",
					"version" => 42,
					"language_codes" => ["en"],
					"domain" => "example.com",
				],
			], \JSON_THROW_ON_ERROR)),
			new MockResponse((string) json_encode([
				"stories" => [
					$this->createStoryData(1, TestStoryComponent::getKey()),
					$this->createStoryData(2, TestStoryComponent::getKey()),
				],
			], \JSON_THROW_ON_ERROR), [
				"response_headers" => [
					"per-page: 2",
					"total: 3",
				],
			]),
			new MockResponse((string) json_encode([
				"stories" => [
					$this->createStoryData(3, TestStoryComponent::getKey()),
				],
			], \JSON_THROW_ON_ERROR), [
				"response_headers" => [
					"per-page: 2",
					"total: 3",
				],
			]),
		];

		$api = $this->createApi(new MockHttpClient($responses));
		$stories = $api->fetchAllStories("news");

		self::assertCount(3, $stories);
		self::assertContainsOnlyInstancesOf(TestStory::class, $stories);
	}

	/**
	 */
	public function testFetchAllStoriesThrowsOnMissingPaginationHeaders () : void
	{
		$responses = [
			new MockResponse((string) json_encode([
				"space" => [
					"id" => 12345,
					"name" => "Test Space",
					"version" => 1,
					"language_codes" => [],
					"domain" => "example.com",
				],
			], \JSON_THROW_ON_ERROR)),
			new MockResponse((string) json_encode([
				"stories" => [],
			], \JSON_THROW_ON_ERROR), [
				"response_headers" => [
					"per-page: invalid",
					"total: invalid",
				],
			]),
		];

		$api = $this->createApi(new MockHttpClient($responses));

		$this->expectException(ContentRequestFailedException::class);
		$this->expectExceptionMessage("invalid response structure / missing headers");

		$api->fetchAllStories("news");
	}

	/**
	 */
	public function testGetSpaceInfoThrowsOnMismatchingSpaceId () : void
	{
		$api = $this->createApi(
			new MockHttpClient(
				static fn () => new MockResponse((string) json_encode([
					"space" => [
						"id" => 99999,
						"name" => "Wrong Space",
						"version" => 7,
						"language_codes" => [],
						"domain" => "example.com",
					],
				], \JSON_THROW_ON_ERROR)),
			),
		);

		$this->expectException(InvalidConfigException::class);
		$api->getSpaceInfo();
	}

	/**
	 */
	public function testFetchStoriesThrowsIfHydratedStoryTypeDoesNotMatchRequestedType () : void
	{
		$responses = [
			new MockResponse((string) json_encode([
				"space" => [
					"id" => 12345,
					"name" => "Test Space",
					"version" => 123,
					"language_codes" => [],
					"domain" => "example.com",
				],
			], \JSON_THROW_ON_ERROR)),
			new MockResponse((string) json_encode([
				"stories" => [
					$this->createStoryData(1, OtherStoryComponent::getKey()),
				],
			], \JSON_THROW_ON_ERROR), [
				"response_headers" => [
					"per-page: 100",
					"total: 1",
				],
			]),
		];

		$api = $this->createApi(new MockHttpClient($responses));

		$this->expectException(InvalidDataException::class);
		$this->expectExceptionMessage("Requested stories for type");

		$api->fetchStories(TestStory::class, "news");
	}

	/**
	 */
	public function testFetchDatasourceEntriesPaginatesAndUsesDimensionValue () : void
	{
		$api = $this->createApi(new MockHttpClient([
			$this->createSpaceInfoResponse(),
			new MockResponse((string) json_encode([
				"datasource_entries" => [
					[
						"name" => "One",
						"value" => "one",
						"dimension_value" => null,
					],
				],
			], \JSON_THROW_ON_ERROR), [
				"response_headers" => [
					"per-page: 1",
					"total: 2",
				],
			]),
			new MockResponse((string) json_encode([
				"datasource_entries" => [
					[
						"name" => "Two",
						"value" => "two",
						"dimension_value" => "zwei",
					],
				],
			], \JSON_THROW_ON_ERROR), [
				"response_headers" => [
					"per-page: 1",
					"total: 2",
				],
			]),
		]));

		$entries = $api->fetchDatasourceEntries("labels", "de");

		self::assertCount(2, $entries);
		self::assertSame("One", $entries["one"]->label);
		self::assertSame("one", $entries["one"]->value);
		self::assertSame("Two", $entries["zwei"]->label);
		self::assertSame("zwei", $entries["zwei"]->value);
	}

	/**
	 */
	public function testFetchFoldersInPathFiltersOnlyFolders () : void
	{
		$api = $this->createApi(new MockHttpClient([
			$this->createSpaceInfoResponse(),
			new MockResponse((string) json_encode([
				"links" => [
					[
						"id" => 1,
						"uuid" => "11111111-1111-1111-1111-111111111111",
						"slug" => "news",
						"name" => "News",
						"is_folder" => true,
						"position" => 10,
					],
					[
						"id" => 2,
						"uuid" => "22222222-2222-2222-2222-222222222222",
						"slug" => "news/article",
						"name" => "Article",
						"is_folder" => false,
						"position" => 20,
					],
				],
			], \JSON_THROW_ON_ERROR), [
				"response_headers" => [
					"per-page: 1000",
					"total: 2",
				],
			]),
		]));

		$folders = $api->fetchFoldersInPath("news");

		self::assertCount(1, $folders);
		self::assertSame("News", $folders[0]->getName());
		self::assertSame("news", $folders[0]->getFullSlug());
	}

	/**
	 */
	public function testFetchFolderTitleMapUsesRelativeLocalPaths () : void
	{
		$api = $this->createApi(new MockHttpClient([
			$this->createSpaceInfoResponse(),
			new MockResponse((string) json_encode([
				"links" => [
					[
						"id" => 1,
						"uuid" => "11111111-1111-1111-1111-111111111111",
						"slug" => "root/news",
						"name" => "News",
						"is_folder" => true,
						"position" => 1,
					],
					[
						"id" => 2,
						"uuid" => "22222222-2222-2222-2222-222222222222",
						"slug" => "root/about",
						"name" => "About",
						"is_folder" => true,
						"position" => 2,
					],
				],
			], \JSON_THROW_ON_ERROR), [
				"response_headers" => [
					"per-page: 1000",
					"total: 2",
				],
			]),
		]));

		$map = $api->fetchFolderTitleMap("root");

		self::assertSame([
			"/news" => "News",
			"/about" => "About",
		], $map);
	}

	/**
	 */
	public function testFetchAllLinksReturnsLinkDtos () : void
	{
		$api = $this->createApi(new MockHttpClient([
			$this->createSpaceInfoResponse(),
			new MockResponse((string) json_encode([
				"links" => [
					[
						"id" => 10,
						"uuid" => "aaaaaaaa-1111-1111-1111-111111111111",
						"slug" => "en/path/item",
						"name" => "Item",
						"is_folder" => false,
						"position" => 5,
					],
				],
			], \JSON_THROW_ON_ERROR), [
				"response_headers" => [
					"per-page: 1000",
					"total: 1",
				],
			]),
		]));

		$links = $api->fetchAllLinks();

		self::assertCount(1, $links);
		self::assertSame("Item", $links[0]->name);
		self::assertSame(["en", "path", "item"], $links[0]->getSlugSegments());
	}

	/**
	 */
	public function testFetchSignedAssetUrlThrowsIfAssetTokenMissing () : void
	{
		$api = $this->createApi(new MockHttpClient([]));

		$this->expectException(MissingConfigException::class);
		$this->expectExceptionMessage("Can't fetch signed asset url without asset token");

		$api->fetchSignedAssetUrl("https://a.storyblok.com/f/123/file.jpg");
	}

	/**
	 */
	public function testFetchSignedAssetUrlReturnsAssetData () : void
	{
		$api = $this->createApi(
			new MockHttpClient([
				new MockResponse((string) json_encode([
					"asset" => [
						"id" => 11,
						"filename" => "https://a.storyblok.com/f/123/file.jpg",
						"signed_url" => "https://signed.example/asset",
					],
				], \JSON_THROW_ON_ERROR)),
			]),
			assetToken: "asset-token",
		);

		$asset = $api->fetchSignedAssetUrl("https://a.storyblok.com/f/123/file.jpg");

		self::assertSame("11", $asset->getId());
		self::assertSame("https://signed.example/asset", $asset->getSignedUrl());
	}

	/**
	 */
	public function testFetchSignedAssetUrlThrowsOnInvalidStructure () : void
	{
		$api = $this->createApi(
			new MockHttpClient([
				new MockResponse((string) json_encode([
					"invalid" => true,
				], \JSON_THROW_ON_ERROR)),
			]),
			assetToken: "asset-token",
		);

		$this->expectException(ContentRequestFailedException::class);
		$this->expectExceptionMessage("invalid response structure");

		$api->fetchSignedAssetUrl("https://a.storyblok.com/f/123/file.jpg");
	}

	/**
	 */
	public function testResetClearsCachedSpaceInfo () : void
	{
		$api = $this->createApi(new MockHttpClient([
			new MockResponse((string) json_encode([
				"space" => [
					"id" => 12345,
					"name" => "First Space",
					"version" => 1,
					"language_codes" => [],
					"domain" => "first.example.com",
				],
			], \JSON_THROW_ON_ERROR)),
			new MockResponse((string) json_encode([
				"space" => [
					"id" => 12345,
					"name" => "Second Space",
					"version" => 2,
					"language_codes" => [],
					"domain" => "second.example.com",
				],
			], \JSON_THROW_ON_ERROR)),
		]));

		self::assertSame("First Space", $api->getSpaceInfo()->getName());
		self::assertSame("First Space", $api->getSpaceInfo()->getName());

		$api->reset();

		self::assertSame("Second Space", $api->getSpaceInfo()->getName());
	}

	/**
	 */
	private function createApi (HttpClientInterface $httpClient, ?string $assetToken = null) : ContentApi
	{
		$componentManager = $this->createComponentManager();
		$context = new ComponentContext(
			$componentManager,
			new DataTransformer(),
			new NullLogger(),
			new DataValidator(),
			new ImageDimensionsExtractor(),
		);
		$storyFactory = new StoryFactory($componentManager, $context, new NullLogger());

		return new ContentApi(
			$httpClient,
			new StoryblokConfig(
				spaceId: "12345",
				managementToken: "management-token",
				contentToken: "content-token",
				assetToken: $assetToken,
			),
			$storyFactory,
			$componentManager,
			new NullLogger(),
		);
	}

	/**
	 */
	private function createComponentManager () : ComponentManager
	{
		return new ComponentManager(new ServiceLocator([
			TestStoryComponent::getKey() => static fn () => new TestStoryComponent(),
			OtherStoryComponent::getKey() => static fn () => new OtherStoryComponent(),
		]));
	}

	/**
	 */
	private function createStoryData (int $id, string $componentKey) : array
	{
		return [
			"id" => $id,
			"name" => "Story {$id}",
			"uuid" => "11111111-1111-1111-1111-" . str_pad((string) $id, 12, "0", \STR_PAD_LEFT),
			"full_slug" => "en/story-{$id}",
			"is_startpage" => false,
			"lang" => "en",
			"created_at" => "2026-03-20T00:00:00.000+00:00",
			"first_published_at" => null,
			"published_at" => null,
			"content" => [
				"_uid" => "uid-{$id}",
				"component" => $componentKey,
				"title" => "Title {$id}",
			],
		];
	}

	/**
	 */
	private function createSpaceInfoResponse () : MockResponse
	{
		return new MockResponse((string) json_encode([
			"space" => [
				"id" => 12345,
				"name" => "Test Space",
				"version" => 42,
				"language_codes" => ["en"],
				"domain" => "example.com",
			],
		], \JSON_THROW_ON_ERROR));
	}
}

final class TestStory extends Story
{
}

final class OtherStory extends Story
{
}

final class TestStoryComponent extends AbstractComponent
{
	#[\Override]
	public static function getKey () : string
	{
		return "test-story";
	}

	#[\Override]
	protected function configureFields () : array
	{
		return [
			"title" => new TextField("Title"),
		];
	}

	#[\Override]
	protected function getComponentType () : ComponentType
	{
		return ComponentType::Standalone;
	}

	#[\Override]
	public function getDisplayName () : string
	{
		return "Test Story";
	}

	#[\Override]
	public function getStoryClass () : string
	{
		return TestStory::class;
	}
}

final class OtherStoryComponent extends AbstractComponent
{
	#[\Override]
	public static function getKey () : string
	{
		return "other-story";
	}

	#[\Override]
	protected function configureFields () : array
	{
		return [
			"title" => new TextField("Title"),
		];
	}

	#[\Override]
	protected function getComponentType () : ComponentType
	{
		return ComponentType::Standalone;
	}

	#[\Override]
	public function getDisplayName () : string
	{
		return "Other Story";
	}

	#[\Override]
	public function getStoryClass () : string
	{
		return OtherStory::class;
	}
}
