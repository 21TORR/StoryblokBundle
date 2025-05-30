<?php declare(strict_types=1);

namespace Torr\Storyblok\TranslationManagement\Data;

final readonly class ComponentData
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

	public function getStringValueForField (string $fieldname) : ?string
	{
		return \is_string($this->data[$fieldname] ?? null) ? $this->data[$fieldname] : null;
	}

	public function isRichTextField (string $fieldname) : bool
	{
		return \is_array($this->data[$fieldname]) && "doc" === $this->data[$fieldname]["type"];
	}

	public function getRichTextValuesForField (string $fieldname) : array
	{
		$texts = [];

		if (!$this->isRichTextField($fieldname))
		{
			return $texts;
		}

		return $this->getTextsFromRichtextData($this->data[$fieldname], $this->getKeyForField($fieldname));
	}

	private function getTextsFromRichtextData (array $data, string $currentPath = "") : array
	{
		$results = [];

		foreach ($data as $key => $value)
		{
			$path = \is_int($key) ? \sprintf("%s[%d]", $currentPath, $key) : \sprintf("%s.%s", $currentPath, $key);

			if ("text" === ($value["type"] ?? null))
			{
				$results[] = [
					"key" => \sprintf("%s.text", $path),
					"value" => $value["text"] ?? null,
				];
			}

			if (\is_array($value))
			{
				$results = [
					...$results,
					...$this->getTextsFromRichtextData($value, $path),
				];
			}
		}

		return $results;
	}
}
