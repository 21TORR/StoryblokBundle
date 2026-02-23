<?php declare(strict_types=1);

namespace Torr\Storyblok\Adapter;

use Psr\Log\LoggerInterface;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Torr\Storyblok\Api\ContentApi;
use Torr\Storyblok\Api\ManagementApi;
use Torr\Storyblok\Api\Transformer\StoryblokIdSlugMapper;
use Torr\Storyblok\Backend\StoryblokBackendUrlGenerator;
use Torr\Storyblok\Config\StoryblokConfig;
use Torr\Storyblok\Manager\ComponentManager;
use Torr\Storyblok\Story\StoryFactory;
use Torr\Storyblok\Webhook\Request\RequestValidator;

abstract class AbstractStoryblokAdapter
{
	public private(set) ContentApi $contentApi;
	public private(set) ManagementApi $managementApi;
	public private(set) StoryblokIdSlugMapper $idSlugMapper;
	public private(set) StoryblokBackendUrlGenerator $storyblokBackendUrlGenerator;
	public private(set) RequestValidator $requestValidator;
	public private(set) string $spaceId;

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

		$this->idSlugMapper = new StoryblokIdSlugMapper($this->contentApi);
		$this->storyblokBackendUrlGenerator = new StoryblokBackendUrlGenerator($config);
		$this->requestValidator = new RequestValidator(
			$config,
			$logger,
		);
		$this->spaceId = (string) $config->getSpaceId();
	}

	/**
	 * @return list<string>
	 */
	abstract public function getStandaloneComponentKeys () : array;

	abstract public function getDisplayName () : string;

	abstract public static function getKey () : string;
}
