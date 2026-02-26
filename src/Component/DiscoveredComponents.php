<?php declare(strict_types=1);

namespace Torr\Storyblok\Component;

final class DiscoveredComponents
{
	/** @var array<string, true> */
	private array $components = [];

	public function markAsDiscovered (AbstractComponent $component) : void
	{
		$this->components[$component::getKey()] = true;
	}

	public function hasAlreadyDiscovered (AbstractComponent $component) : bool
	{
		return $this->components[$component::getKey()] ?? false;
	}

	/**
	 * @return list<string>
	 */
	public function getAllDiscoveredComponents () : array
	{
		return array_keys($this->components);
	}
}
