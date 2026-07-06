<?php declare(strict_types=1);

namespace Torr\Storyblok\Definition\Data;

use Torr\Storyblok\Field\FieldType;

/**
 * @final
 */
readonly class FieldDefinition
{
	/**
	 */
	public function __construct (
		public string $key,
		public string $label,
		public FieldType $type,
		public array $data,
	) {}
}
