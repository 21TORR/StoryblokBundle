<?php declare(strict_types=1);

namespace Torr\Storyblok\Config;

/**
 * @final
 */
readonly class StoryblokBundleConfig
{
	/**
	 */
	public function __construct (
		public bool $syncDefinitionsOnAppDeployInStaging,
		public bool $syncDefinitionsOnAppDeployInProduction,
	) {}
}
