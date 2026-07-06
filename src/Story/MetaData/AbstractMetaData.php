<?php declare(strict_types=1);

namespace Torr\Storyblok\Story\MetaData;

/**
 */
readonly abstract class AbstractMetaData
{
	/**
	 */
	public function __construct (
		public string $uuid,
		/**
		 * The component type of the story's component
		 */
		public string $type,
		public string $spaceId,
		public ?string $previewData,
	) {}
}
