<?php declare(strict_types=1);

namespace Torr\Storyblok\Field\Definition;

use Torr\Storyblok\Field\FieldType;
use Torr\Storyblok\Validator\DataValidator;

final class BooleanField extends AbstractField
{
	/**
	 * @inheritDoc
	 */
	protected function getInternalStoryblokType () : FieldType
	{
		return FieldType::Boolean;
	}

	/**
	 * @inheritDoc
	 */
	public function validateData (DataValidator $validator, array $path, mixed $data) : void
	{
		// @todo add implementation
	}
}
