<?php declare(strict_types=1);

namespace Torr\Storyblok\TranslationManagement\Data;

final readonly class TranslationDataElement
{
	public function __construct (
		private string $key,
		private string $type,
		private ?string $value,
	) {}

	/**
	 * @return string Jsonpath
	 */
	public function getKey () : string
	{
		return $this->key;
	}

	public function getType () : string
	{
		return $this->type;
	}

	public function getValue () : ?string
	{
		return $this->value;
	}
}
