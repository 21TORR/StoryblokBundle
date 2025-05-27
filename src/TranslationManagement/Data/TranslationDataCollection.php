<?php declare(strict_types=1);

namespace Torr\Storyblok\TranslationManagement\Data;

use Torr\Storyblok\TranslationManagement\Service\NormalizerInterface;

final readonly class TranslationDataCollection
{
	/**
	 * @param list<TranslationDataElement> $data
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
	 * @return list<TranslationDataElement>
	 */
	public function getData () : array
	{
		return $this->data;
	}

	public function normalize (NormalizerInterface $exportService) : string
	{
		return $exportService->normalize($this);
	}
}
