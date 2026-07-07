<?php declare(strict_types=1);

namespace Torr\Storyblok\Debug;

use Torr\Storyblok\Component\Config\ComponentType;

use function Symfony\Component\String\u;

/**
 * @final
 */
readonly class DebugInfoCliRenderer
{
	/**
	 *
	 */
	public function renderComponentType (ComponentType $componentType) : string
	{
		return match ($componentType)
		{
			ComponentType::Nested => "<fg=blue>nested</>",
			ComponentType::Standalone => "<fg=magenta>standalone</>",
		};
	}

	/**
	 *
	 */
	public function renderClassName (?string $className, bool $showFQCN = false) : string
	{
		if (null === $className)
		{
			return "<fg=gray>—</>";
		}

		if (!$showFQCN)
		{
			$className = u($className)->afterLast("\\")->toString();
		}

		return \sprintf("<fg=blue>%s</>", $className);
	}
}
