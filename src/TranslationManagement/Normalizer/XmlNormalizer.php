<?php declare(strict_types=1);

namespace Torr\Storyblok\TranslationManagement\Normalizer;

use Torr\Storyblok\TranslationManagement\Data\TranslationDataCollection;
use Torr\Storyblok\TranslationManagement\Exception\XmlExportException;

final readonly class XmlNormalizer implements NormalizerInterface
{
	#[\Override]
	public function normalize (TranslationDataCollection $data) : string
	{
		$dom = new \DOMDocument('1.0', 'UTF-8');
		$dom->formatOutput = true;

		$page = $dom->createElement('page');

		$page->setAttribute("id", $data->getId());
		$page->setAttribute("url", $data->getUrl());
		$page->setAttribute("language", $data->getLanguage());
		$page->setAttribute("filename", $data->getFilename());

		$dom->appendChild($page);

		$name = $dom->createElement("name");

		$name->appendChild($dom->createTextNode($data->getName()));

		$page->appendChild($name);

		$tags = $dom->createElement('tags');

		$page->appendChild($tags);

		foreach ($data->getData() as $tagData)
		{
			$tag = $dom->createElement('tag');

			$tag->setAttribute('id', $tagData->getKey());
			$tag->setAttribute('type', $tagData->getType());

			$text = $dom->createElement('text');

			if (null !== $tagData->getValue())
			{
				$cdata = $dom->createCDATASection($tagData->getValue());
				$text->appendChild($cdata);
			}

			$tag->appendChild($text);

			$tags->appendChild($tag);
		}

		return $dom->saveXML() ?: throw new XmlExportException("XML Export failed");
	}
}
