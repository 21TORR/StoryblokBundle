<?php declare(strict_types=1);

namespace Torr\Storyblok\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @final
 */
readonly class StoryblokBundleConfiguration implements ConfigurationInterface
{
	/**
	 *
	 */
	#[\Override]
	public function getConfigTreeBuilder () : TreeBuilder
	{
		$treeBuilder = new TreeBuilder("storyblok");

		$treeBuilder->getRootNode()
			->children()
				->arrayNode("sync_definitions_on_app_deploy")
					->addDefaultsIfNotSet()
					->beforeNormalization()
						->ifTrue(\is_bool(...))
						->then(static fn (bool $value) => [
							"staging" => $value,
							"production" => $value,
						])
					->end()
					->children()
						->booleanNode("staging")
							->defaultValue(false)
						->end()
						->booleanNode("production")
							->defaultValue(false)
						->end()
					->end()
				->end()
			->end();

		return $treeBuilder;
	}
}
