<?php declare(strict_types=1);

namespace Torr\Storyblok\Mapping\FieldAttribute;

abstract readonly class FieldAttributeInterface
{
	/**
	 */
	public function __construct (
		public array $managementApiData,
	) {}
}
