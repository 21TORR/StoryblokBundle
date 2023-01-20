<?php declare(strict_types=1);

namespace Torr\Storyblok\Field\Choices;

use Torr\Storyblok\Context\ComponentContext;

/**
 * Connects the choice field to a JSON in a remote service
 */
final class RemoteJsonChoices implements ChoicesInterface
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

	/**
	 * @inheritDoc
	 */
	public function isValidData (
		array|int|string $data,
		?ComponentContext $context = null,
	) : bool
	{
		return true;
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
