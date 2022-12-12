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
		/**
		 * @see https://www.storyblok.com/docs/technical-limits
		 */
		$container->prependExtensionConfig("framework", [
			"rate_limiter" => [
				"storyblok_management" => [
					"policy" => "fixed_window",
					// the limit is 3 for free plans. It is likely that a debug implementation uses a free dummy space in Storyblok
					"limit" => $container->getParameter("kernel.debug")
						? 3
						: 6,
					"interval" => "1 second",
				],
				"storyblok_content_delivery" => [
					"policy" => "fixed_window",
					"limit" => 1000,
					"interval" => "1 second",
				],
			],
		]);
	}
}
