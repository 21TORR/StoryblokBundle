<?php declare(strict_types=1);

namespace Torr\Storyblok\Mapping\FieldAttribute;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
final readonly class WithValidation extends FieldAttributeInterface
{
	/**
	 */
	public function __construct (
		bool $required = true,
		?string $regexp = null,
		public bool $allowMissingData = false,
	)
	{
		parent::__construct([
			"required" => $required,
			"regex" => $regexp,
		]);
	}
}
