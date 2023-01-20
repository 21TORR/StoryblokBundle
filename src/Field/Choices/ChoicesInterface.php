<?php declare(strict_types=1);

namespace Torr\Storyblok\Field\Choices;

use Torr\Storyblok\Context\ComponentContext;

interface ChoicesInterface
{
	/**
	 * Returns the data for the management API
	 */
	public function toManagementApiData () : array;

	/**
	 * Validates the given data.
	 */
	public function isValidData (
		array|int|string $data,
		?ComponentContext $context = null,
	) : bool;

	/**
	 * Transforms the storyblok data the app's representation
	 */
	public function transformData (
		ComponentContext $context,
		array|int|string $data,
	) : mixed;
}
