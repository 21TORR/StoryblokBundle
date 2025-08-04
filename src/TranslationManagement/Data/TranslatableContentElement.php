<?php declare(strict_types=1);

namespace Torr\Storyblok\TranslationManagement\Data;

final class TranslatableContentElement
{
	public function __construct (
		private readonly string $key,
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

	public function setValue (?string $value) : void
	{
		$this->value = $value;
	}
}
