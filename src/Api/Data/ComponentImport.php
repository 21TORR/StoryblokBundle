<?php declare(strict_types=1);

namespace Torr\Storyblok\Api\Data;

final class ComponentImport
{
	/**
	 */
	public function __construct (
		public readonly string $formattedLabel,
		public readonly array $config,
	) {}

	/**
	 */
	public function getName () : string
	{
		return $this->config["name"];
	}
}
