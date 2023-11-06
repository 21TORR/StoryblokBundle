<?php declare(strict_types=1);

namespace Torr\Storyblok\Cache;

use Symfony\Contracts\Cache\CacheInterface;

/**
 * @template DataType of mixed
 */
final class DebugCache
{
	private bool $hasLocaleCache = false;
	private mixed $localCache = null;
	/** @var callable */
	private mixed $itemGenerator;

	public function __construct (
		private readonly CacheInterface $cache,
		private readonly string $cacheKey,
		callable $itemGenerator,
		private readonly bool $isDebug,
	)
	{
		$this->itemGenerator = $itemGenerator;
	}


	/**
	 * @return DataType
	 */
	public function get () : mixed
	{
		if ($this->isDebug)
		{
			if ($this->hasLocaleCache)
			{
				return $this->localCache;
			}

			$this->hasLocaleCache = true;
			return $this->localCache = ($this->itemGenerator)();
		}

		return $this->cache->get($this->cacheKey, $this->itemGenerator);
	}
}
