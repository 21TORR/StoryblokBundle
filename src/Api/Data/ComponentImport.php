<?php declare(strict_types=1);

namespace Torr\Storyblok\Api\Data;

final class ComponentImport
{
	/**
	 */
	public function __construct (
		public readonly array $config,
		public readonly ?string $groupLabel,
	) {}
}
