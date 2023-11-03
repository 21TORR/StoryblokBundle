<?php declare(strict_types=1);

namespace Torr\Storyblok\Mapping\FieldAttribute;

use Symfony\Component\Validator\Constraint;

abstract readonly class FieldAttributeInterface
{
	/**
	 */
	public function __construct (
		public array $managementApiData,
	) {}

	/**
	 * Validates the given data
	 *
	 * @return Constraint[]
	 */
	public function getValidationConstraints () : array
	{
		return [];
	}
}
