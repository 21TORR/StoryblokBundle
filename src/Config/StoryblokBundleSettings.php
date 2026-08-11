<?php declare(strict_types=1);

namespace Torr\Storyblok\Config;

/**
 * @final
 */
readonly class StoryblokBundleSettings
{
	/**
	 */
	public function __construct (
		public bool $automaticallySyncDefinitionsInStaging,
		public bool $automaticallySyncDefinitionsInProduction,
	) {}
}
