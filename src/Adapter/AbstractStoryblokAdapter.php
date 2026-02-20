<?php declare(strict_types=1);

namespace Torr\Storyblok\Adapter;

use Psr\Log\LoggerInterface;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Torr\Storyblok\Api\ContentApi;
use Torr\Storyblok\Api\ManagementApi;
use Torr\Storyblok\Config\StoryblokConfig;
use Torr\Storyblok\Manager\ComponentManager;
use Torr\Storyblok\Story\StoryFactory;

abstract class AbstractStoryblokAdapter
{
	public private(set) ContentApi $contentApi;
	public private(set) ManagementApi $managementApi;

	public function __construct (
		StoryblokConfig $config,
		HttpClientInterface $client,
		StoryFactory $storyFactory,
		ComponentManager $componentManager,
		RateLimiterFactoryInterface $storyblokManagementLimiter,
		LoggerInterface $logger,
	)
	{
		$this->contentApi = new ContentApi(
			$client,
			$config,
			$storyFactory,
			$componentManager,
			$logger,
		);

		$this->managementApi = new ManagementApi(
			$config,
			$client,
			$storyblokManagementLimiter,
			$logger,
		);
	}

	/**
	 * @return list<string>
	 */
	abstract public function getStandaloneComponentKeys () : array;

	abstract public function getDisplayName () : string;

	abstract public static function getKey () : string;
}
