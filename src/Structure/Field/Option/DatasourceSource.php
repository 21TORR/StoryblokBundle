<?php declare(strict_types=1);

namespace Torr\Storyblok\Structure\Field\Option;

final class DatasourceSource implements ChoiceSourceInterface
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
