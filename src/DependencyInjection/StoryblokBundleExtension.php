<?php declare(strict_types=1);

namespace Torr\Storyblok\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Torr\BundleHelpers\Bundle\ConfigurableBundleExtension;

final class StoryblokBundleExtension extends ConfigurableBundleExtension implements PrependExtensionInterface
{
	/**
	 * @inheritDoc
	 */
	public function prepend (ContainerBuilder $container) : void
	{
	}
}
