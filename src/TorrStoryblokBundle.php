<?php declare(strict_types=1);

namespace Torr\Storyblok;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Torr\Storyblok\Adapter\AbstractStoryblokAdapter;
use Torr\Storyblok\Adapter\StoryblokAdapterRegistry;
use Torr\Storyblok\Component\AbstractComponent;
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
			->addTag(StoryblokAdapterRegistry::DI_TAG);
	}

	/**
	 * @inheritDoc
	 */
	public function getPath () : string
	{
		return \dirname(__DIR__);
	}
}
