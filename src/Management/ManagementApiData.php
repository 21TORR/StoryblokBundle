<?php declare(strict_types=1);

namespace Torr\Storyblok\Management;

use Torr\Storyblok\Exception\InvalidComponentConfigurationException;

final class ManagementApiData
{
	private array $fields = [];

	/**
	 * Adds a field
	 */
	public function registerField (string $key, array $config) : void
	{
		if (\array_key_exists($key, $this->fields))
		{
			throw new InvalidComponentConfigurationException(\sprintf(
				"Invalid component configuration: field key '%s' used more than once",
				$key,
			));
		}

		$config["pos"] = \count($this->fields);
		$this->fields[$key] = $config;
	}

	/**
	 * Returns the full config
	 */
	public function getFullConfig () : array
	{
		return $this->fields;
	}
}
