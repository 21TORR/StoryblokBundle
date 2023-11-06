<?php declare(strict_types=1);

namespace Torr\Storyblok\Story\MetaData;

class ContentMetaData
{
	/**
	 */
	public function __construct (
		private readonly string $uuid,
		private readonly string $type,
		private readonly ?string $previewData = null,
	) {}

	/**
	 *
	 */
	public function getUuid () : string
	{
		return $this->uuid;
	}

	/**
	 *
	 */
	public function getType () : string
	{
		return $this->type;
	}

	/**
	 *
	 */
	public function getPreviewData () : ?string
	{
		return $this->previewData;
	}
}
