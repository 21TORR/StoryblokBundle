<?php declare(strict_types=1);

namespace Torr\Storyblok\Field\Option;

final class LanguagesSource implements ChoiceSourceInterface
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
}
