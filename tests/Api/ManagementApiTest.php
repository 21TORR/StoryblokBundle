<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Api;

use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\RateLimiter\LimiterInterface;
use Symfony\Component\RateLimiter\RateLimit;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;
use Torr\Storyblok\Api\Data\ApiActionPerformed;
use Torr\Storyblok\Api\ManagementApi;
use Torr\Storyblok\Config\StoryblokConfig;
use Torr\Storyblok\Exception\Api\ApiRequestFailedException;
use Torr\Storyblok\Exception\Api\DatasourceSyncFailedException;
use Torr\Storyblok\Exception\Api\TranslationsXmlFileImportFailedException;

/**
 * @internal
 */
final class ManagementApiTest extends TestCase
{
	/**
	 */
	public function testSyncComponentCreatesNewComponentWithPost () : void
	{
		$requests = [];
		$api = $this->createApi(new MockHttpClient(static function (string $method, string $url, array $options) use (&$requests) : MockResponse
		{
			$requests[] = [$method, $url, $options];

			return match (\count($requests))
			{
				1 => new MockResponse((string) json_encode([
					"components" => [],
				], \JSON_THROW_ON_ERROR)),
				2 => new MockResponse((string) json_encode([
					"component" => [
						"name" => "teaser",
						"id" => 123,
					],
				], \JSON_THROW_ON_ERROR)),
				default => throw new \RuntimeException("Unexpected request"),
			};
		}));

		$result = $api->syncComponent([
			"name" => "teaser",
			"schema" => [],
		]);

		self::assertSame(ApiActionPerformed::ADDED, $result);
		self::assertSame("GET", $requests[0][0]);
		self::assertStringEndsWith("/components", $requests[0][1]);
		self::assertSame("POST", $requests[1][0]);
		self::assertStringEndsWith("/components", $requests[1][1]);
	}

	/**
	 */
	public function testSyncComponentUpdatesExistingComponentWithPut () : void
	{
		$requests = [];
		$api = $this->createApi(new MockHttpClient(static function (string $method, string $url, array $options) use (&$requests) : MockResponse
		{
			$requests[] = [$method, $url, $options];

			return match (\count($requests))
			{
				1 => new MockResponse((string) json_encode([
					"components" => [
						[
							"id" => 77,
							"name" => "teaser",
						],
					],
				], \JSON_THROW_ON_ERROR)),
				2 => new MockResponse((string) json_encode([
					"component" => [
						"name" => "teaser",
						"id" => 77,
					],
				], \JSON_THROW_ON_ERROR)),
				default => throw new \RuntimeException("Unexpected request"),
			};
		}));

		$result = $api->syncComponent([
			"name" => "teaser",
			"schema" => [],
		]);

		self::assertSame(ApiActionPerformed::UPDATED, $result);
		self::assertSame("PUT", $requests[1][0]);
		self::assertStringEndsWith("/components/77", $requests[1][1]);
	}

	/**
	 */
	public function testFetchAllAssetsPaginates () : void
	{
		$responses = [
			new MockResponse((string) json_encode([
				"assets" => [
					["id" => 10],
					["id" => 11],
				],
			], \JSON_THROW_ON_ERROR), [
				"response_headers" => [
					"per-page: 2",
					"total: 3",
				],
			]),
			new MockResponse((string) json_encode([
				"assets" => [
					["id" => 12],
				],
			], \JSON_THROW_ON_ERROR), [
				"response_headers" => [
					"per-page: 2",
					"total: 3",
				],
			]),
		];

		$api = $this->createApi(new MockHttpClient($responses));
		$assets = $api->fetchAllAssets();

		self::assertCount(3, $assets);
		self::assertSame("10", $assets[0]->getId());
		self::assertSame("12", $assets[2]->getId());
	}

	/**
	 */
	public function testFetchAllAssetsThrowsOnMissingPaginationHeaders () : void
	{
		$api = $this->createApi(new MockHttpClient([
			new MockResponse((string) json_encode([
				"assets" => [],
			], \JSON_THROW_ON_ERROR)),
		]));

		$this->expectException(ApiRequestFailedException::class);
		$this->expectExceptionMessage("no pagination headers were returned");

		$api->fetchAllAssets();
	}

	/**
	 */
	public function testSendRequestErrorsAreWrapped () : void
	{
		$api = $this->createApi(new MockHttpClient(
			static fn () => new MockResponse("Server Error", [
				"http_code" => 500,
			]),
		));

		$this->expectException(ApiRequestFailedException::class);
		$this->expectExceptionMessage("Failed management request GET 'datasource_entries'");

		$api->fetchDatasourceEntries("colors");
	}

	/**
	 */
	public function testGetOrCreatedComponentGroupUuidReturnsExistingWithoutCreateCall () : void
	{
		$requests = [];
		$api = $this->createApi(new MockHttpClient(static function (string $method, string $url, array $options) use (&$requests) : MockResponse
		{
			$requests[] = [$method, $url, $options];

			return new MockResponse((string) json_encode([
				"component_groups" => [
					[
						"name" => "Content",
						"uuid" => "existing-uuid",
					],
				],
				"components" => [],
			], \JSON_THROW_ON_ERROR));
		}));

		self::assertSame("existing-uuid", $api->getOrCreatedComponentGroupUuid("Content"));
		self::assertCount(1, $requests);
		self::assertSame("GET", $requests[0][0]);
		self::assertStringEndsWith("/components", $requests[0][1]);
	}

	/**
	 */
	public function testGetOrCreatedComponentGroupUuidCreatesAndCachesGroup () : void
	{
		$requests = [];
		$api = $this->createApi(new MockHttpClient(static function (string $method, string $url, array $options) use (&$requests) : MockResponse
		{
			$requests[] = [$method, $url, $options];

			return match (\count($requests))
			{
				1 => new MockResponse((string) json_encode([
					"component_groups" => [],
					"components" => [],
				], \JSON_THROW_ON_ERROR)),
				2 => new MockResponse((string) json_encode([
					"component_group" => [
						"uuid" => "new-uuid",
					],
				], \JSON_THROW_ON_ERROR)),
				default => throw new \RuntimeException("Unexpected request"),
			};
		}));

		self::assertSame("new-uuid", $api->getOrCreatedComponentGroupUuid("New Group"));
		self::assertSame("new-uuid", $api->getOrCreatedComponentGroupUuid("New Group"));
		self::assertCount(2, $requests);
		self::assertSame("POST", $requests[1][0]);
		self::assertStringEndsWith("/component_groups", $requests[1][1]);
	}

	/**
	 */
	public function testSyncDatasourceEntriesAddsAndUpdatesEntries () : void
	{
		$requests = [];
		$api = $this->createApi(new MockHttpClient(static function (string $method, string $url, array $options) use (&$requests) : MockResponse
		{
			$requests[] = [$method, $url, $options];

			return match (\count($requests))
			{
				1 => new MockResponse((string) json_encode([
					"datasources" => [
						[
							"id" => 9,
							"slug" => "labels",
						],
					],
				], \JSON_THROW_ON_ERROR)),
				2 => new MockResponse((string) json_encode([
					"datasource_entries" => [
						[
							"id" => 11,
							"name" => "Old Name",
							"value" => "existing-value",
						],
					],
				], \JSON_THROW_ON_ERROR)),
				default => new MockResponse((string) json_encode([
					"ok" => true,
				], \JSON_THROW_ON_ERROR)),
			};
		}));

		$api->syncDatasourceEntries("labels", [
			"existing-value" => "New Name",
			"new-value" => "New Entry",
		]);

		self::assertSame("POST", $requests[2][0]);
		self::assertStringEndsWith("/datasource_entries", $requests[2][1]);
		self::assertSame("PUT", $requests[3][0]);
		self::assertStringEndsWith("/datasource_entries/11", $requests[3][1]);
	}

	/**
	 */
	public function testSyncDatasourceEntriesThrowsOnDuplicateName () : void
	{
		$api = $this->createApi(new MockHttpClient([
			new MockResponse((string) json_encode([
				"datasources" => [
					[
						"id" => 9,
						"slug" => "labels",
					],
				],
			], \JSON_THROW_ON_ERROR)),
			new MockResponse((string) json_encode([
				"datasource_entries" => [
					[
						"id" => 11,
						"name" => "Duplicate Name",
						"value" => "existing",
					],
				],
			], \JSON_THROW_ON_ERROR)),
		]));

		$this->expectException(DatasourceSyncFailedException::class);
		$this->expectExceptionMessage("Duplicate datasource name");

		$api->syncDatasourceEntries("labels", [
			"new-value" => "Duplicate Name",
		]);
	}

	/**
	 */
	public function testFetchAssetFoldersBuildsHierarchy () : void
	{
		$api = $this->createApi(new MockHttpClient([
			new MockResponse((string) json_encode([
				"asset_folders" => [
					[
						"id" => 1,
						"name" => "Root",
						"uuid" => "root-uuid",
						"parent_id" => 0,
					],
					[
						"id" => 2,
						"name" => "Child",
						"uuid" => "child-uuid",
						"parent_id" => 1,
					],
				],
			], \JSON_THROW_ON_ERROR)),
		]));

		$tree = $api->fetchAssetFolders();
		$root = $tree->getFolderById(1);
		$child = $tree->getFolderByUuid("child-uuid");

		self::assertNotNull($root);
		self::assertNotNull($child);
		self::assertSame($root, $child->parent);
		self::assertSame($root, $child->getRootFolder());
	}

	/**
	 */
	public function testExportTranslationsXmlFileUsesQueryAndReturnsContent () : void
	{
		$requests = [];
		$api = $this->createApi(new MockHttpClient(static function (string $method, string $url, array $options) use (&$requests) : MockResponse
		{
			$requests[] = [$method, $url, $options];

			return new MockResponse("<xliff/>");
		}));

		$result = $api->exportTranslationsXmlFile("42", "en");

		self::assertSame("<xliff/>", $result);
		self::assertSame("GET", $requests[0][0]);
		self::assertStringContainsString("/stories/42/export.xml", $requests[0][1]);
		self::assertSame("en", $requests[0][2]["query"]["lang_code"]);
	}

	/**
	 */
	public function testImportTranslationsXmlFileSendsJsonBody () : void
	{
		$requests = [];
		$api = $this->createApi(new MockHttpClient(static function (string $method, string $url, array $options) use (&$requests) : MockResponse
		{
			$requests[] = [$method, $url, $options];

			return new MockResponse("");
		}));

		$api->importTranslationsXmlFile("42", "<xliff/>");

		self::assertSame("PUT", $requests[0][0]);
		self::assertStringEndsWith("/stories/42/import.xml", $requests[0][1]);
		self::assertStringContainsString("\"data\":\"<xliff\\/>\"", $requests[0][2]["body"]);
	}

	/**
	 */
	public function testImportTranslationsXmlFileWrapsThrownExceptions () : void
	{
		$api = $this->createApi(new MockHttpClient(
			static fn () => new MockResponse("Server Error", [
				"http_code" => 500,
			]),
		));

		$this->expectException(TranslationsXmlFileImportFailedException::class);

		$api->importTranslationsXmlFile("42", "<xliff/>");
	}

	/**
	 */
	private function createApi (MockHttpClient $httpClient) : ManagementApi
	{
		$limiter = self::createStub(LimiterInterface::class);
		$limiter->method("consume")->willReturn(new RateLimit(
			availableTokens: 1,
			retryAfter: new \DateTimeImmutable("-1 second"),
			accepted: true,
			limit: 1,
		));

		$rateLimiterFactory = self::createStub(RateLimiterFactoryInterface::class);
		$rateLimiterFactory
			->method("create")
			->willReturn($limiter);

		return new ManagementApi(
			new StoryblokConfig(
				spaceId: "12345",
				managementToken: "management-token",
				contentToken: "content-token",
			),
			$httpClient,
			$rateLimiterFactory,
			new NullLogger(),
		);
	}
}
