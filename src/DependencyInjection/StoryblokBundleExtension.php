<?php declare(strict_types=1);

namespace Torr\Storyblok\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Torr\BundleHelpers\Bundle\BundleExtension;
use Torr\Storyblok\Config\StoryblokBundleConfig;

final class StoryblokBundleExtension extends BundleExtension implements PrependExtensionInterface
{
	/**
	 */
	#[\Override]
	public function load (array $configs, ContainerBuilder $container) : void
	{
		parent::load($configs, $container);

		$config = $this->processConfiguration(new StoryblokBundleConfiguration(), $configs);

		$container->getDefinition(StoryblokBundleConfig::class)
			->setArgument('$syncDefinitionsOnAppDeployInStaging', $config["sync_definitions_on_app_deploy"]["staging"])
			->setArgument('$syncDefinitionsOnAppDeployInProduction', $config["sync_definitions_on_app_deploy"]["production"]);
	}

	/**
	 * @inheritDoc
	 */
	#[\Override]
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
					"policy" => "sliding_window",
					// Contradicting to the Technical Limits docs, the docs of the Content API says that the rate limit is 50 requests per second,
					// according to https://www.storyblok.com/docs/api/content-delivery/v2#topics/rate-limit
					"limit" => 50,
					"interval" => "1 second",
				],
			],
		]);
	}
}
