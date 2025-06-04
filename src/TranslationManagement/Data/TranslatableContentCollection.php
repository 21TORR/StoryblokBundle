<?php declare(strict_types=1);

namespace Torr\Storyblok\TranslationManagement\Data;

/**
 * @implements \IteratorAggregate<int, TranslatableContentElement>
 */
final readonly class TranslatableContentCollection implements \IteratorAggregate
{
	/**
	 * @param list<TranslatableContentElement> $data
	 */
	public function __construct (
		private string $id,
		private string $filename,
		private string $url,
		private string $language,
		private string $name,
		private array $data,
	) {}

	public function getId () : string
	{
		return $this->id;
	}

	public function getFilename () : string
	{
		return $this->filename;
	}

	public function getUrl () : string
	{
		return $this->url;
	}

	public function getLanguage () : string
	{
		return $this->language;
	}

	public function getName () : string
	{
		return $this->name;
	}

	/**
	 * @return list<TranslatableContentElement>
	 */
	public function getData () : array
	{
		return $this->data;
	}

	/**
	 * @return \Traversable<TranslatableContentElement>
	 */
	public function getIterator () : \Traversable
	{
		return new \ArrayIterator($this->data);
	}
}
