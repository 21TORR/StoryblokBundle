<?php declare(strict_types=1);

namespace Torr\Storyblok\Structure\OptionsConfiguration;

use Torr\Storyblok\Data\Option;
use Torr\Storyblok\Exception\InvalidOptionConfigurationException;

final class StaticOptionsConfiguration implements OptionsConfiguration
{
	/**
	 * @param Option[]|array<array{name: string, value: string|int}> $options
	 */
	public function __construct (
		public readonly array $options,
	)
	{
		if (0 === \count($this->options))
		{
			throw new InvalidOptionConfigurationException("You have to pass at least one option.");
		}
	}

	public function getSerializedConfig () : array
	{
		$options = [];

		foreach ($this->options as $option)
		{
			if ($option instanceof Option)
			{
				$options[] = [
					"name" => $option->label,
					"value" => $option->value,
				];
			}
			else {
				$options[] = $option;
			}
		}

		return [
			"options" => $options,
		];
	}
}
