<?php declare(strict_types=1);

namespace Torr\Storyblok\TranslationManagement\Data;

final readonly class TranslatableComponentData
{
	public function __construct (
		private string $id,
		private string $key,
		private array $data,
	) {}

	public function getId () : string
	{
		return $this->id;
	}

	public function getKey () : string
	{
		return $this->key;
	}

	public function getData () : array
	{
		return $this->data;
	}

	/**
	 * @return string Jsonpath
	 */
	public function getKeyForField (string $fieldname) : string
	{
		return \sprintf("$..[?(@['_uid']=='%s')]['%s']", $this->id, $fieldname);
	}

	public function getJsonValueForField (string $fieldname) : ?string
	{
		return $this->data[$fieldname] ?? null ? json_encode($this->data[$fieldname], \JSON_THROW_ON_ERROR) : null;
	}

	public function getStringValueForField (string $fieldname) : ?string
	{
		return \is_string($this->data[$fieldname] ?? null) ? $this->data[$fieldname] : null;
	}

	public function isRichTextField (string $fieldname) : bool
	{
		return self::isRichText($this->data[$fieldname] ?? null);
	}

	public static function isRichText (mixed $data) : bool
	{
		return \is_array($data) && "doc" === ($data["type"] ?? null);
	}
}
