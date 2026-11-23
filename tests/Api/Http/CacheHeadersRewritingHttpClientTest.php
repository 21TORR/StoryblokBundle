<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Api\Http;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Symfony\Component\HttpClient\CachingHttpClient;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpClient\Retry\GenericRetryStrategy;
use Symfony\Component\HttpClient\RetryableHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Torr\Storyblok\Api\Http\CacheHeadersRewritingHttpClient;

/**
 * Tests the client together with the {@see CachingHttpClient}, as the rewritten headers only matter for it.
 *
 * @internal
 */
final class CacheHeadersRewritingHttpClientTest extends TestCase
{
	private const array PUBLISHED = ["cv" => 1, "version" => "published"];

	/**
	 */
	public function testPublishedRequestWithCacheVersionIsHardCached () : void
	{
		$inner = new MockHttpClient([
			// body in multiple chunks, with headers that would prevent caching
			new MockResponse(['{"a":', "1}"], [
				"response_headers" => [
					"Cache-Control: no-cache, max-age=0",
					"ETag: \"abc\"",
					"Total: 5",
				],
			]),
		]);
		$client = $this->createClient($inner);

		$first = $client->request("GET", "https://example.test/stories", ["query" => self::PUBLISHED]);
		self::assertSame(["a" => 1], $first->toArray());

		$second = $client->request("GET", "https://example.test/stories", ["query" => self::PUBLISHED]);
		self::assertSame(200, $second->getStatusCode());
		self::assertSame(["a" => 1], $second->toArray());
		self::assertSame(["5"], $second->getHeaders()["total"]);
		self::assertSame(1, $inner->getRequestsCount());
	}

	/**
	 */
	public function testDifferentCacheVersionsAreCachedSeparately () : void
	{
		$inner = new MockHttpClient([
			new MockResponse('{"v":1}'),
			new MockResponse('{"v":2}'),
		]);
		$client = $this->createClient($inner);

		self::assertSame(["v" => 1], $client->request("GET", "https://example.test/stories", ["query" => ["cv" => 1, "version" => "published"]])->toArray());
		self::assertSame(["v" => 2], $client->request("GET", "https://example.test/stories", ["query" => ["cv" => 2, "version" => "published"]])->toArray());
		self::assertSame(["v" => 1], $client->request("GET", "https://example.test/stories", ["query" => ["cv" => 1, "version" => "published"]])->toArray());
		self::assertSame(2, $inner->getRequestsCount());
	}

	/**
	 */
	public static function provideUncachedRequests () : iterable
	{
		yield "no cv" => ["GET", ["version" => "published"]];
		yield "draft" => ["GET", ["cv" => 1, "version" => "draft"]];
		yield "no version" => ["GET", ["cv" => 1]];
		yield "post" => ["POST", self::PUBLISHED];
	}

	/**
	 */
	#[DataProvider("provideUncachedRequests")]
	public function testUncachedRequests (string $method, array $query) : void
	{
		$inner = new MockHttpClient(static fn () => new MockResponse("{}", [
			"response_headers" => ["Cache-Control: public, max-age=3600"],
		]));
		$client = $this->createClient($inner);

		$client->request($method, "https://example.test/stories", ["query" => $query])->getContent();
		$client->request($method, "https://example.test/stories", ["query" => $query])->getContent();

		self::assertSame(2, $inner->getRequestsCount());
	}

	/**
	 */
	public static function provideErrorResponsesAreNotCached () : iterable
	{
		yield "not found" => [404];
		yield "rate limited" => [429];
		yield "server error" => [503];
	}

	/**
	 */
	#[DataProvider("provideErrorResponsesAreNotCached")]
	public function testErrorResponsesAreNotCached (int $statusCode) : void
	{
		$inner = new MockHttpClient(static fn () => new MockResponse("{}", [
			"http_code" => $statusCode,
			"response_headers" => ["Cache-Control: public, max-age=3600"],
		]));
		$client = $this->createClient($inner);

		self::assertSame($statusCode, $client->request("GET", "https://example.test/stories", ["query" => self::PUBLISHED])->getStatusCode());
		self::assertSame($statusCode, $client->request("GET", "https://example.test/stories", ["query" => self::PUBLISHED])->getStatusCode());
		self::assertSame(2, $inner->getRequestsCount());
	}

	/**
	 * Rate limited responses are retried, only the final response may be cached
	 */
	public function testRetriedRateLimitedResponseIsNotCached () : void
	{
		$inner = new MockHttpClient([
			new MockResponse('{"error":"rate limited"}', [
				"http_code" => 429,
				"response_headers" => ["Cache-Control: public, max-age=3600"],
			]),
			new MockResponse('{"a":1}'),
		]);
		$client = $this->createClient(new RetryableHttpClient($inner, new GenericRetryStrategy(delayMs: 0)));

		self::assertSame(["a" => 1], $client->request("GET", "https://example.test/stories", ["query" => self::PUBLISHED])->toArray());
		self::assertSame(["a" => 1], $client->request("GET", "https://example.test/stories", ["query" => self::PUBLISHED])->toArray());
		self::assertSame(2, $inner->getRequestsCount());
	}

	/**
	 */
	private function createClient (HttpClientInterface $inner) : CachingHttpClient
	{
		return new CachingHttpClient(
			new CacheHeadersRewritingHttpClient($inner, 3600),
			new TagAwareAdapter(new ArrayAdapter()),
			maxTtl: 3600,
		);
	}
}
