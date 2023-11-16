<?php declare(strict_types=1);

namespace Torr\Storyblok\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class StoryblokBundleConfiguration implements ConfigurationInterface
{
	/**
	 * @inheritDoc
	 */
	public function getConfigTreeBuilder () : TreeBuilder
	{
		$treeBuilder = new TreeBuilder("storyblok");

		$treeBuilder->getRootNode()
			->children()
				->integerNode("space_id")
					->defaultNull()
				->end()
				->scalarNode("management_token")
					->defaultNull()
				->end()
				->scalarNode("content_token")
					->defaultNull()
				->end()
				->integerNode("locale_level")
					->defaultValue(0)
					->info("The slug level that includes the locales (0-based).")
				->end()
				->scalarNode("components_preview_image_path")
					->defaultValue("assets/storyblok/component-preview")
					->info("The path within Symfony's /public/ directory. This path is being used by the ComponentAssetsHelper to generate URLs to component preview images.")
				->end()
			->end();

		return $treeBuilder;
	}
}
