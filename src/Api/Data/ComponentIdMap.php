<?php declare(strict_types=1);

namespace Torr\Storyblok\Api\Data;

final class ComponentIdMap
{
	/** @var array<string, string> */
	private array $componentGroups;

	/** @var array<string, int> */
	private array $components;

	/**
	 */
	public function __construct (array $response)
	{
		foreach (($response["component_groups"] ?? []) as $group)
		{
			$this->registerComponentGroup($group["name"], $group["uuid"]);
		}

		foreach (($response["components"] ?? []) as $component)
		{
			$this->registerComponent($component["name"], $component["id"]);
		}
	}

	/**
	 *
	 */
	public function registerComponentGroup (string $name, string $uuid) : void
	{
		$this->componentGroups[$name] = $uuid;
	}

	/**
	 *
	 */
	public function registerComponent (string $key, int $id) : void
	{
		$this->components[$key] = $id;
	}


	/**
	 *
	 */
	public function getComponentId (string $key) : ?int
	{
		return $this->components[$key] ?? null;
	}

	/**
	 *
	 */
	public function getGroupUuid (string $name) : ?string
	{
		return $this->componentGroups[$name] ?? null;
	}

	/**
	 * @return string[]
	 */
	public function getAllComponentKeys () : array
	{
		return \array_keys($this->components);
	}
}
