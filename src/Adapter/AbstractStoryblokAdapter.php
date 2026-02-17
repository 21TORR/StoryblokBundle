<?php declare(strict_types=1);

namespace Torr\Storyblok\Adapter;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Torr\Storyblok\Api\ContentApi;
use Torr\Storyblok\Api\ManagementApi;
use Torr\Storyblok\Component\AbstractComponent;
use Torr\Storyblok\Config\StoryblokConfig;
use Torr\Storyblok\Manager\ComponentManager;
use Torr\Storyblok\Story\StoryFactory;

abstract class AbstractStoryblokAdapter
{
	public ContentApi $contentApi;
	public ManagementApi $managementApi;

	/**
	 * @param string $spaceId         To be defined in extending class as dependency injection
	 * @param string $managementToken To be defined in extending class as dependency injection
	 * @param string $contentToken    To be defined in extending class as dependency injection
	 * @param string $webhookSecret   To be defined in extending class as dependency injection
	 */
	public function __construct (
		string $spaceId,
		string $managementToken,
		string $contentToken,
		string $webhookSecret,
		HttpClientInterface $client,
		StoryFactory $storyFactory,
		ComponentManager $componentManager,
		LoggerInterface $logger,
	)
	{
		$config = new StoryblokConfig(
			spaceId: $spaceId,
			managementToken: $managementToken,
			contentToken: $contentToken,
			webhookSecret: $webhookSecret,
		);

		$this->contentApi = new ContentApi(
			$client,
			$config,
			$storyFactory,
			$componentManager,
			$logger,
		);
	}

	/**
	 * @return list<class-string<AbstractComponent>>
	 */
	abstract public function getStandaloneComponents () : array;

	/**
	 *
	 */
	abstract public static function getKey () : string;

	/**
	 *
	 */
	abstract public function getDisplayName () : string;
}
