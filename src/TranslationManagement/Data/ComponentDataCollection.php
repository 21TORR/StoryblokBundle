<?php declare(strict_types=1);

namespace Torr\Storyblok\TranslationManagement\Data;

use Torr\Storyblok\TranslationManagement\Validator\ComponentDataValidator;

final readonly class ComponentDataCollection
{
	/** @var array<string, ComponentData> */
	private array $data;

	public function __construct (
		array $story,
	)
	{
		$this->data = $this->extractComponents($story);
	}

	public function getData () : array
	{
		return $this->data;
	}

	/**
	 * @return array<string, ComponentData>
	 */
	private function extractComponents (array $storyData) : array
	{
		$components = [];

		foreach ($storyData as $entry)
		{
			if (\is_array($entry))
			{
				if (ComponentDataValidator::isValid($entry))
				{
					$components[$entry["_uid"]] = new ComponentData($entry["_uid"], $entry["component"], $entry);
				}

				$components = [
					...$components,
					...$this->extractComponents($entry),
				];
			}
		}

		return $components;
	}
}
