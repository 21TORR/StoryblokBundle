<?php declare(strict_types=1);

namespace Torr\Storyblok\TranslationManagement\Data;

use Torr\Storyblok\TranslationManagement\Validator\ComponentDataValidator;

/**
 * @implements \IteratorAggregate<int, TranslatableComponentData>
 */
final readonly class TranslatableComponentDataCollection implements \IteratorAggregate
{
	/** @var array<string, TranslatableComponentData> */
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
	 * @return \Traversable<TranslatableComponentData>
	 */
	public function getIterator () : \Traversable
	{
		return new \ArrayIterator($this->data);
	}

	/**
	 * @return array<string, TranslatableComponentData>
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
					\assert(\is_string($entry["_uid"]));
					\assert(\is_string($entry["component"]));

					$components[$entry["_uid"]] = new TranslatableComponentData($entry["_uid"], $entry["component"], $entry);
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
