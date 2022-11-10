<?php declare(strict_types=1);

namespace Torr\Storyblok\Data\OptionsConfiguration;

use Torr\Storyblok\Api\Manager\ComponentManager;
use Torr\Storyblok\Data\StoryblokOption;
use Torr\Storyblok\Data\StoryblokOptionsConfiguration;
use Torr\Storyblok\Exception\InvalidOptionConfigurationException;

final class StoryblokStaticOptionsConfiguration implements StoryblokOptionsConfiguration
{
	/**
	 * @param StoryblokOption[]|array<array{name: string, value: string|int}> $options
	 */
	public function __construct (
		private readonly array $options,
	)
	{
		if (0 === \count($this->options))
		{
			throw new InvalidOptionConfigurationException("You have to pass at least one option.");
		}
	}

	/**
	 * @return StoryblokOption[]|array<array{name: string, value: string|int}>
	 */
	public function getOptions () : array
	{
		return $this->options;
	}

	public function getSchemaDefinition (ComponentManager $componentManager) : array
	{
		$options = [];

		foreach ($this->getOptions() as $option)
		{
			if ($option instanceof StoryblokOption)
			{
				$options[] = [
					"name" => $option->getLabel(),
					"value" => $option->getValue(),
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
