<?php declare(strict_types=1);

namespace Torr\Storyblok\Field\Choices;

use Torr\Storyblok\Context\ComponentContext;

/**
 * Choice list that lists all configured languages in storyblok
 */
final class LanguagesChoices implements ChoicesInterface
{
	/**
	 */
	public function __construct (
		private readonly bool $showEmptyOption = true,
	) {}

	/**
	 * @inheritDoc
	 */
	public function toManagementApiData () : array
	{
		return [
			"source" => "internal_languages",
			"exclude_empty_option" => !$this->showEmptyOption,
		];
	}

	/**
	 * @inheritDoc
	 */
	public function getValidationConstraints (bool $allowMultiple) : array
	{
		// always valid
		return [];
	}

	/**
	 * @inheritDoc
	 */
	public function transformData (
		ComponentContext $context,
		array|int|string $data,
	) : mixed
	{
		return $data;
	}
}
