<?php declare(strict_types=1);

namespace Torr\Storyblok\Api\Http;

use Symfony\Component\HttpClient\AsyncDecoratorTrait;
use Symfony\Component\HttpClient\CachingHttpClient;
use Symfony\Component\HttpClient\Response\AsyncContext;
use Symfony\Component\HttpClient\Response\AsyncResponse;
use Symfony\Contracts\HttpClient\ChunkInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Torr\Storyblok\Release\ReleaseVersion;

/**
 * Rewrites the cache headers of the Storyblok responses, so that the {@see CachingHttpClient} on top
 * only caches what we want it to cache:
 *
 * - successful (200) GET requests for published content with a `cv` (cache version) are hard-cached. As the
 *   cache version changes whenever content is published, these responses are immutable.
 * - everything else is marked as `no-store`: requests without `cv` (like the space info, which provides the `cv`),
 *   draft requests (they still send the `cv`, like the Storyblok UI does) and all non-200 responses.
 *
 * The latter is important, as Storyblok also sends its regular cache headers with error responses (e.g. when rate
 * limiting with a 429). These must never be cached, otherwise all following requests for this URL would fail
 * until the cache version changes.
 *
 * The headers are rewritten as soon as they arrive, the body is streamed through unchanged.
 *
 * @internal
 */
final class CacheHeadersRewritingHttpClient implements HttpClientInterface
{
	use AsyncDecoratorTrait;

	/**
	 * Headers that influence the freshness or revalidation of a response, which are dropped
	 */
	private const array REPLACED_HEADERS = [
		"age",
		"cache-control",
		"etag",
		"expires",
		"last-modified",
		"pragma",
	];

	/**
	 * @param positive-int $maxTtl
	 */
	public function __construct (
		HttpClientInterface $client,
		private readonly int $maxTtl,
	)
	{
		$this->client = $client;
	}

	/**
	 * @inheritDoc
	 */
	#[\Override]
	public function request (string $method, string $url, array $options = []) : ResponseInterface
	{
		$isCacheableRequest = self::isCacheableRequest($method, $url, $options);
		$maxTtl = $this->maxTtl;

		return new AsyncResponse(
			$this->client,
			$method,
			$url,
			$options,
			static function (ChunkInterface $chunk, AsyncContext $context) use ($isCacheableRequest, $maxTtl) : \Generator
			{
				if (null !== $chunk->getError() || $chunk->isTimeout() || !$chunk->isFirst())
				{
					yield $chunk;

					return;
				}

				$cacheControl = $isCacheableRequest && 200 === $context->getStatusCode()
					? "public, max-age={$maxTtl}, immutable"
					: "no-store";

				// The CachingHttpClient decides about caching using the raw response headers
				/** @var list<string> $rawHeaders */
				$rawHeaders = $context->getInfo("response_headers") ?? [];
				$headers = [];

				foreach ($rawHeaders as $header)
				{
					if (!self::isReplacedHeader($header))
					{
						$headers[] = $header;
					}
				}

				$headers[] = "Cache-Control: {$cacheControl}";

				$context->setInfo("response_headers", $headers);
				$context->passthru();

				yield $chunk;
			},
		);
	}

	/**
	 * Returns whether the given raw header line is one of the replaced headers
	 */
	private static function isReplacedHeader (string $header) : bool
	{
		$parts = explode(":", $header, 2);

		return 2 === \count($parts)
			&& \in_array(strtolower(trim($parts[0])), self::REPLACED_HEADERS, true);
	}

	/**
	 * Only GET requests for published content with a cache version can be cached
	 */
	private static function isCacheableRequest (string $method, string $url, array $options) : bool
	{
		if ("GET" !== $method)
		{
			return false;
		}

		$query = \is_array($options["query"] ?? null)
			? $options["query"]
			: [];
		$urlQuery = parse_url($url, \PHP_URL_QUERY);

		if (\is_string($urlQuery))
		{
			parse_str($urlQuery, $parsedUrlQuery);
			$query = [...$parsedUrlQuery, ...$query];
		}

		if (!isset($query["cv"]) || "" === $query["cv"])
		{
			return false;
		}

		return ReleaseVersion::PUBLISHED->value === ($query["version"] ?? null);
	}
}
