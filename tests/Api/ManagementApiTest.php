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
	private function createApi (MockHttpClient $httpClient) : ManagementApi
	{
		$limiter = $this->createStub(LimiterInterface::class);
		$limiter->method("consume")->willReturn(new RateLimit(
			availableTokens: 1,
			retryAfter: new \DateTimeImmutable("-1 second"),
			accepted: true,
			limit: 1,
		));

		$rateLimiterFactory = $this->createStub(RateLimiterFactoryInterface::class);
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
