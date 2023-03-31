<?php declare(strict_types=1);

namespace Torr\Storyblok\Field\Choices;

use Symfony\Component\Validator\Constraints\Choice;
use Torr\Storyblok\Context\ComponentContext;

/**
 * Defines the choice list items statically.
 */
class StaticChoices implements ChoicesInterface
{
	/**
	 * @param array<string, string|int> $choices Mapping of choice label to value
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

	/**
	 * @inheritDoc
	 */
	public function transformData (
		ComponentContext $context,
		array|int|string $data,
	) : mixed
	{
		// we can just pass the value through.
		return $data;
	}

	/**
	 * @inheritDoc
	 */
	public function isValidData (
		array|int|string $data,
		?ComponentContext $context = null,
	) : bool
	{
		$values = \is_array($data) ? $data : [$data];

		foreach ($values as $value)
		{
			if (!\in_array($value, $this->choices, true))
			{
				return false;
			}
		}

		return true;
	}
}
