<?php declare(strict_types=1);

namespace Torr\Storyblok\Story\MetaData;

final class BlokMetaData
{
	private readonly string $uid;
	private readonly ?string $previewData;
	private readonly string $type;

	/**
	 */
	public function __construct (
		array $data

	)
	{
		$this->uid = $data["_uid"];
		$this->type = $data["component"];
		$this->previewData = $data["_editable"];
	}

	/**
	 *
	 */
	public function getUid () : string
	{
		return $this->uid;
	}

	/**
	 *
	 */
	public function getPreviewData () : ?string
	{
		return $this->previewData;
	}

	/**
	 *
	 */
	public function getType () : string
	{
		return $this->type;
	}
}
