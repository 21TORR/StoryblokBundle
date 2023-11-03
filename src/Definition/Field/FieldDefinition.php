<?php declare(strict_types=1);

namespace Torr\Storyblok\Definition\Field;

use Torr\Storyblok\Mapping\Field\AbstractField;
use Torr\Storyblok\Mapping\FieldAttribute\FieldAttributeInterface;

final class FieldDefinition
{
	public function __construct (
		public readonly AbstractField $field,
		/** @var FieldAttributeInterface[] */
		private readonly array $attributes = [],
	) {}

	/**
	 *
	 */
	public function generateManagementApiData () : array
	{
		$data = $this->field->generateManagementApiData();

		foreach ($this->attributes as $attribute)
		{
			foreach ($attribute->managementApiData as $key => $value)
			{
				$data[$key] = $value;
			}
		}

		return $data;
	}
}
