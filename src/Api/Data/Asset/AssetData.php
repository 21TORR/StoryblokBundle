<?php declare(strict_types=1);

namespace Torr\Storyblok\Api\Data\Asset;

/**
 * @final
 */
readonly class AssetData
{
	/**
	 */
	public function __construct (
		private array $data,
	) {}

	/**
	 *
	 */
	public function getId () : int
	{
		return $this->data["id"];
	}

	/**
	 *
	 */
	public function getUrl () : string
	{
		return $this->data["filename"];
	}

	/**
	 *
	 */
	public function getFilename () : string
	{
		return $this->data["short_filename"];
	}

	/**
	 *
	 */
	public function getSpaceId () : int
	{
		return $this->data["space_id"];
	}

	/**
	 *
	 */
	public function getFolderId () : ?int
	{
		return $this->data["asset_folder_id"];
	}

	/**
	 *
	 */
	public function getFileSize () : int
	{
		return $this->data["content_length"];
	}

	/**
	 *
	 */
	public function getMimeType () : string
	{
		return $this->data["content_type"];
	}

	/**
	 *
	 */
	public function isPrivate () : bool
	{
		return $this->data["is_private"];
	}

	/**
	 */
	public function getInternalTags () : array
	{
		return $this->data["internal_tags_list"];
	}
}
