<?php declare(strict_types=1);

namespace Torr\Storyblok\Cache;

use Symfony\Contracts\Cache\CacheInterface;

final readonly class DebugCacheFactory
{
	/**
	 */
	public function __construct (
		private CacheInterface $cache,
		private bool $isDebug,
	) {}


	/**
	 * Creates a new debug cache
	 */
	public function createCache (
		string $cacheKey,
		callable $itemGenerator,
	) : DebugCache
	{
		return new DebugCache(
			$this->cache,
			$cacheKey,
			$itemGenerator,
			$this->isDebug,
		);
	}
}
