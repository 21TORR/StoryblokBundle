<?php declare(strict_types=1);

namespace Torr\Storyblok\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class StoryblokBundleConfiguration implements ConfigurationInterface
{
	/**
	 * @inheritDoc
	 */
	public function getConfigTreeBuilder ()
	{
		$treeBuilder = new TreeBuilder("storyblok");

		$treeBuilder->getRootNode()
			->children()
				->scalarNode("space_id")
					->defaultNull()
				->end()
				->scalarNode("management_token")
					->defaultNull()
				->end()
				->scalarNode("content_token")
					->defaultNull()
				->end()
			->end();

		return $treeBuilder;
	}
}
