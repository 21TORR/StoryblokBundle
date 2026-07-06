<?php declare(strict_types=1);

namespace Torr\Storyblok\Story\MetaData;

/**
 * @final
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
		protected array $data,
		public ?string $previewData,
	) {}
}
