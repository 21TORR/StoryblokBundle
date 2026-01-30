<?php declare(strict_types=1);

namespace Torr\Storyblok;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Torr\Storyblok\Api\Adapter\AbstractStoryblokAdapter;
use Torr\Storyblok\Component\AbstractComponent;
use Torr\Storyblok\Config\StoryblokConfig;
use Torr\Storyblok\DependencyInjection\StoryblokBundleConfiguration;
use Torr\Storyblok\DependencyInjection\StoryblokBundleExtension;

final class TorrStoryblokBundle extends Bundle
{
	/**
	 * @inheritDoc
	 */
	public function getContainerExtension () : ExtensionInterface
	{
		return new StoryblokBundleExtension(
			$this,
			new StoryblokBundleConfiguration(),
			static function (array $config, ContainerBuilder $container) : void
			{
				$container->getDefinition(StoryblokConfig::class)
					->setArgument('$spaceId', $config["space_id"])
					->setArgument('$managementToken', $config["management_token"])
					->setArgument('$contentToken', $config["content_token"])
					->setArgument('$localeLevel', $config["locale_level"])
					->setArgument('$webhookSecret', $config["webhook"]["secret"])
					->setArgument('$allowUrlWebhookSecret', $config["webhook"]["allow_url_secret"]);
			},
			"storyblok",
		);
	}

	/**
	 * @inheritDoc
	 */
	public function build (ContainerBuilder $container) : void
	{
		$container->registerForAutoconfiguration(AbstractComponent::class)
			->addTag("storyblok.component.definition");
		$container->registerForAutoconfiguration(AbstractStoryblokAdapter::class)
			->addTag("storyblok.adapter.definition");
	}

	/**
	 * @inheritDoc
	 */
	public function getPath () : string
	{
		return \dirname(__DIR__);
	}
}
