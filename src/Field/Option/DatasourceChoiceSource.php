<?php declare(strict_types=1);

namespace Torr\Storyblok\Field\Option;

/**
 * Connects the choice field to a datasource in storyblok
 */
final class DatasourceChoiceSource implements ChoiceSourceInterface
{
	/**
	 */
	public function __construct (
		private readonly string $datasourceSlug,
		private readonly bool $showEmptyOption = true,
	) {}

	/**
	 * @inheritDoc
	 */
	public function toManagementApiData () : array
	{
		return [
			"source" => "internal",
			"datasource_slug" => $this->datasourceSlug,
			"exclude_empty_option" => !$this->showEmptyOption,
		];
	}
}
