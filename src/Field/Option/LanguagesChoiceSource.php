<?php declare(strict_types=1);

namespace Torr\Storyblok\Field\Option;

/**
 * Choice list that lists all configured languages in storyblok
 */
final class LanguagesChoiceSource implements ChoiceSourceInterface
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
