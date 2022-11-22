<?php declare(strict_types=1);

namespace Torr\Storyblok\Validator;

use Symfony\Component\Validator\Constraint;
use Torr\Storyblok\Data\FieldType;

final class DataValidator
{
	/**
	 * Ensures that the given data is valid
	 *
	 * @param string[] $contentPath The path to the given content element
	 * @param array<Constraint|null> $constraints
	 * @return void|never
	 */
	public function ensureDataIsValid (
		array $contentPath,
		FieldType $fieldType,
		mixed $data,
		array $constraints,
	) : void
	{
	}
}