<?php declare(strict_types=1);

namespace Torr\Storyblok\Field\Option;

/**
 * Connects the choice field to a JSON in a remote service
 */
final class RemoteJsonChoiceSource implements ChoiceSourceInterface
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
