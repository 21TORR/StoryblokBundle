<?php declare(strict_types=1);

namespace Torr\Storyblok\Api\Data\Asset;

/**
 * @final
 */
class AssetFolder
{
	public private(set) ?self $parent = null;

	/**
	 */
	public function __construct (
		public readonly int $id,
		public readonly string $name,
		public readonly string $uuid,
		public private(set) array $children = [],
	) {}

	/**
	 * @return $this
	 */
	public function addChild (self $child) : static
	{
		$child->parent = $this;
		$this->children[] = $child;

		return $this;
	}

	/**
	 *
	 */
	public function getHierarchy () : array
	{
		$cursor = $this;
		$hierarchy = [];

		while (null !== $cursor)
		{
			$hierarchy[] = $cursor;
			$cursor = $cursor->parent;
		}

		return array_reverse($hierarchy);
	}

	/**
	 *
	 */
	public function getRootFolder () : self
	{
		return $this->getHierarchy()[0];
	}
}
