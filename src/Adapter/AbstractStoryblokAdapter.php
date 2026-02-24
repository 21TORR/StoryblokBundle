<?php declare(strict_types=1);

namespace Torr\Storyblok\Adapter;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Torr\Storyblok\Api\ContentApi;
use Torr\Storyblok\Api\ManagementApi;
use Torr\Storyblok\Api\Transformer\StoryblokIdSlugMapper;
use Torr\Storyblok\Config\StoryblokConfig;
use Torr\Storyblok\Manager\ComponentManager;
use Torr\Storyblok\Story\StoryFactory;
use Torr\Storyblok\Webhook\Request\RequestValidator;

abstract class AbstractStoryblokAdapter implements ServiceSubscriberInterface
{
	public readonly ContentApi $contentApi;
	public readonly ManagementApi $managementApi;
	public readonly StoryblokIdSlugMapper $idSlugMapper;
	public readonly RequestValidator $requestValidator;
	public readonly string $spaceId;

	public function __construct (
		ContainerInterface $locator,
		/** @internal */
		public readonly StoryblokConfig $config,
	)
	{
		$client = $locator->get(HttpClientInterface::class);
		\assert($client instanceof HttpClientInterface);

		$storyFactory = $locator->get(StoryFactory::class);
		\assert($storyFactory instanceof StoryFactory);

		$logger = $locator->get(LoggerInterface::class);
		\assert($logger instanceof LoggerInterface);

		$componentManager = $locator->get(ComponentManager::class);
		\assert($componentManager instanceof ComponentManager);

		$rateLimiterFactory = $locator->get("limiter.storyblok_management");
		\assert($rateLimiterFactory instanceof RateLimiterFactoryInterface);

		$this->contentApi = new ContentApi(
			$client,
			$this->config,
			$storyFactory,
			$componentManager,
			$logger,
		);

		$this->managementApi = new ManagementApi(
			$this->config,
			$client,
			$rateLimiterFactory,
			$logger,
		);

		$this->idSlugMapper = new StoryblokIdSlugMapper($this->contentApi);
		$this->requestValidator = new RequestValidator(
			$this->config,
			$logger,
		);
		$this->spaceId = $this->config->spaceId;
	}

	/**
	 * @inheritDoc
	 */
	#[\Override]
	public static function getSubscribedServices() : array
	{
		return [
			HttpClientInterface::class,
			StoryFactory::class,
			ComponentManager::class,
			"limiter.storyblok_management" => RateLimiterFactoryInterface::class,
			LoggerInterface::class,
		];
	}

	/**
	 * @return list<string>
	 */
	abstract public function getStandaloneComponentKeys () : array;

	abstract public function getDisplayName () : string;

	abstract public static function getKey () : string;
}
