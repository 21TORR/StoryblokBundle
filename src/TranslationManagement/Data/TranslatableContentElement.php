<?php declare(strict_types=1);

namespace Torr\Storyblok\TranslationManagement\Data;

final readonly class TranslatableContentElement
{
	public function __construct (
		private string $key,
		private ?string $value = null,
	) {}

	/**
	 * @return string Jsonpath
	 */
	public function getKey () : string
	{
		return $this->key;
	}

	public function getValue () : ?string
	{
		return $this->value;
	}
}
