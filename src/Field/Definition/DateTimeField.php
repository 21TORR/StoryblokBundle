<?php declare(strict_types=1);

namespace Torr\Storyblok\Field\Definition;

use Torr\Storyblok\Field\FieldType;
use Torr\Storyblok\Validator\DataValidator;

final class DateTimeField extends AbstractField
{
	/**
	 * @inheritDoc
	 */
	public function __construct (
		string $label,
		private readonly bool $withTimeSelection = true,
		mixed $defaultValue = null,
	)
	{
		parent::__construct($label, $defaultValue);
	}

	/**
	 * @inheritDoc
	 */
	public function toManagementApiData (int $position) : array
	{
		return \array_replace(
			parent::toManagementApiData($position),
			[
				"disable_time" => !$this->withTimeSelection,
			],
		);
	}

	/**
	 * @inheritDoc
	 */
	protected function getInternalStoryblokType () : FieldType
	{
		return FieldType::DateTime;
	}

	/**
	 * @inheritDoc
	 */
	public function validateData (DataValidator $validator, array $contentPath, mixed $data) : void
	{
		// @todo add implementation
	}
}
