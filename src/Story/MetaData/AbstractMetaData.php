<?php declare(strict_types=1);

namespace Torr\Storyblok\Story\MetaData;

/**
 */
abstract readonly class AbstractMetaData
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
		public ?string $previewData = null,
	) {}
}
