<?php declare(strict_types=1);

namespace Torr\Storyblok\Structure\Field;

use Torr\Storyblok\Data\FieldType;
use Torr\Storyblok\Validator\DataValidator;

final class TableField extends AbstractField
{
	/**
	 * @inheritDoc
	 */
	protected function getInternalStoryblokType () : FieldType
	{
		return FieldType::Table;
	}

	/**
	 * @inheritDoc
	 */
	public function validateData (DataValidator $validator, array $path, mixed $data) : void
	{
		// @todo add implementation
	}
}
