<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Webhook\Fixtures;

use Psr\Container\ContainerInterface;
use Torr\Storyblok\Adapter\AbstractStoryblokAdapter;
use Torr\Storyblok\Config\StoryblokConfig;

final class TestWebhookAdapter extends AbstractStoryblokAdapter
{
	public function __construct (ContainerInterface $locator, StoryblokConfig $config)
	{
		parent::__construct($locator, $config);
	}

	#[\Override]
	public function getStandaloneComponentKeys () : array
	{
		return [];
	}

	#[\Override]
	public function getDisplayName () : string
	{
		return "Test Webhook Adapter";
	}

	#[\Override]
	public static function getKey () : string
	{
		return "test-webhook";
	}
}
