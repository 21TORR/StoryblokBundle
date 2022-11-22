<?php declare(strict_types=1);

namespace Torr\Storyblok\Field\Option;

final class RemoteJsonSource implements ChoiceSourceInterface
{
	/**
	 */
	public function __construct (
		private readonly string $url,
		private readonly bool $showEmptyOption = true,
	) {}

	/**
	 * @inheritDoc
	 */
	public function toManagementApiData () : array
	{
		return [
			"source" => "external",
			"exclude_empty_option" => !$this->showEmptyOption,
			"external_datasource" => $this->url,
		];
	}

}
