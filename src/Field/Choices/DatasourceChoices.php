<?php declare(strict_types=1);

namespace Torr\Storyblok\Field\Choices;

use Torr\Storyblok\Context\ComponentContext;

/**
 * Connects the choice field to a datasource in storyblok
 */
final class DatasourceChoices implements ChoicesInterface
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

	/**
	 * @inheritDoc
	 */
	public function isValidData (
		int|string $data,
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
		int|string $data,
	) : mixed
	{
		return $data;
	}
}
