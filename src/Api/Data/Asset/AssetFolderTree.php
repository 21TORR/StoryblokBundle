<?php declare(strict_types=1);

namespace Torr\Storyblok\Api\Data\Asset;

/**
 * @final
 */
readonly class AssetFolderTree
{
	/** @var array<int, AssetFolder> */
	private array $idMap;

	/** @var array<string, AssetFolder> */
	private array $uuidMap;

	/** @var list<AssetFolder> */
	private array $rootFolders;

	/**
	 * @param AssetFolder[] $folders
	 */
	public function __construct (
		array $folders,
	)
	{
		$idMap = [];
		$uuidMap = [];
		$rootFolders = [];

		foreach ($folders as $folder)
		{
			$idMap[$folder->id] = $folder;
			$uuidMap[$folder->uuid] = $folder;

			if (null === $folder->parent)
			{
				$rootFolders[] = $folder;
			}
		}

		$this->idMap = $idMap;
		$this->uuidMap = $uuidMap;
		$this->rootFolders = $rootFolders;
	}

	/**
	 *
	 */
	public function getFolderById (int $id) : ?AssetFolder
	{
		return $this->idMap[$id] ?? null;
	}
}
