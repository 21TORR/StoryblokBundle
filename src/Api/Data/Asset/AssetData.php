<?php declare(strict_types=1);

namespace Torr\Storyblok\Api\Data\Asset;

use Torr\Storyblok\Exception\Api\Data\InvalidAssetMetadataException;

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
	public function getId () : string
	{
		return (string) $this->data["id"];
	}

	/**
	 *
	 */
	public function getOriginUrl () : string
	{
		return $this->data["filename"];
	}

	/**
	 * Returns the frontend display URL.
	 *
	 * This method doesn't check if the file is public
	 */
	public function getFrontendUrl () : string
	{
		return str_replace(
			"https://s3.amazonaws.com/a.storyblok.com/",
			"https://a.storyblok.com/",
			$this->getOriginUrl(),
		);
	}

	/**
	 *
	 */
	public function getFilename () : string
	{
		if (\array_key_exists("short_filename", $this->data))
	{
		return $this->data["short_filename"];
	}

		return \basename($this->getOriginUrl());
	}

	/**
	 *
	 */
	public function getSpaceId () : string
	{
		return (string) $this->data["space_id"];
	}

	/**
	 *
	 */
	public function getFolderId () : ?string
	{
		return null !== $this->data["asset_folder_id"]
			? (string) $this->data["asset_folder_id"]
			: null;
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
	 *
	 */
	public function getCreatedAt () : \DateTimeImmutable
	{
		return $this->parseDate($this->data["created_at"]);
	}

	/**
	 *
	 */
	public function getUpdatedAt () : \DateTimeImmutable
	{
		return $this->parseDate($this->data["updated_at"]);
	}

	/**
	 *
	 */
	public function getContentType () : string
	{
		return $this->data["content_type"];
	}

	/**
	 *
	 */
	public function getContentLength () : int
	{
		return $this->data["content_length"];
	}

	/**
	 *
	 */
	public function getAlt () : ?string
	{
		return $this->normalizeString($this->data["alt"]);
	}

	/**
	 *
	 */
	public function getCopyright () : ?string
	{
		return $this->normalizeString($this->data["copyright"]);
	}

	/**
	 *
	 */
	public function getSource () : ?string
	{
		return $this->normalizeString($this->data["source"]);
	}

	/**
	 *
	 */
	public function getTitle () : ?string
	{
		return $this->normalizeString($this->data["title"]);
	}

	/**
	 *
	 */
	public function getFocus () : ?string
	{
		return $this->normalizeString($this->data["focus"]);
	}

	/**
	 *
	 */
	public function getTags () : array
	{
		return array_map(
			static fn (array $tag) => $tag["name"],
			$this->data["internal_tags_list"],
		);
	}

	/**
	 *
	 */
	public function isLocked () : bool
	{
		return $this->data["locked"];
	}

	/**
	 *
	 */
	public function getMetaData () : array
	{
		return $this->data["meta_data"];
	}

	/**
	 *
	 */
	private function parseDate (string $value) : \DateTimeImmutable
	{
		$parsed = \DateTimeImmutable::createFromFormat("!Y-m-d\TH:i:s.ve", $value);

		if (!$parsed)
		{
			throw new InvalidAssetMetadataException(\sprintf(
				"Could not parse value '%s' as date",
				$value,
			));
		}

		return $parsed;
	}

	/**
	 *
	 */
	private function normalizeString (string $value) : ?string
	{
		return "" !== $value
			? $value
			: null;
	}
}
