<?php declare(strict_types=1);

namespace Torr\Storyblok\Field\Choices;

use Symfony\Component\Validator\Constraint;
use Torr\Storyblok\Context\ComponentContext;

interface ChoicesInterface
{
	/**
	 * Returns the data for the management API
	 */
	public function toManagementApiData () : array;

	/**
	 * Returns the constraints to validate the data.
	 *
	 * @return Constraint[]
	 */
	public function getValidationConstraints (bool $allowMultiple) : array;

	/**
	 * Transforms the storyblok data the app's representation
	 */
	public function transformData (
		ComponentContext $context,
		array|int|string $data,
	) : mixed;
}
