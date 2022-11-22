<?php declare(strict_types=1);

namespace Torr\Storyblok\Field\Definition;

use Torr\Storyblok\Field\FieldType;
use Torr\Storyblok\Field\Option\ChoiceSourceInterface;
use Torr\Storyblok\Validator\DataValidator;

final class ChoiceField extends AbstractField
{
	public function __construct (
		string $label,
		private readonly ChoiceSourceInterface $source,
		private readonly bool $allowMultiselect = false,
		mixed $defaultValue = null,
	)
	{
		parent::__construct($label, $defaultValue);
	}

	/**
	 * @inheritDoc
	 */
	protected function getInternalStoryblokType () : FieldType
	{
		return $this->allowMultiselect
			? FieldType::Options
			: FieldType::Option;
	}

	/**
	 * @inheritDoc
	 */
	public function toManagementApiData (int $position) : array
	{
		return \array_replace(
			parent::toManagementApiData($position),
			$this->source->toManagementApiData(),
		);
	}

	/**
	 * @inheritDoc
	 */
	public function validateData (DataValidator $validator, array $path, mixed $data, ) : void
	{
	}

}
