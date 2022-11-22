<?php declare(strict_types=1);

namespace Torr\Storyblok\Structure\Field\Option;

final class StaticSource implements ChoiceSourceInterface
{
	/**
	 * @param array<string, string> $choices Mapping of choice label to value
	 */
	public function __construct (
		private readonly array $choices,
		private readonly bool $showEmptyOption = true,
	) {}


	/**
	 * @inheritDoc
	 */
	public function toManagementApiData () : array
	{
		$formattedOptions = [];

		foreach ($this->choices as $name => $value)
		{
			$formattedOptions[] = [
				"name" => $name,
				"value" => $value,
			];
		}

		return [
			"options" => $formattedOptions,
			"exclude_empty_option" => !$this->showEmptyOption,
		];
	}
}
