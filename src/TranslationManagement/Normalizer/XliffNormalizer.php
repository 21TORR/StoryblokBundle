<?php declare(strict_types=1);

namespace Torr\Storyblok\TranslationManagement\Normalizer;

use Torr\Storyblok\TranslationManagement\Data\TranslatableContentCollection;
use Torr\Storyblok\TranslationManagement\Data\TranslatableContentElement;
use Torr\Storyblok\TranslationManagement\Exception\XliffExportException;
use Torr\Storyblok\TranslationManagement\Exception\XliffInvalidException;

final readonly class XliffNormalizer
{
	/**
	 * @param array{targetLanguage: string} $options
	 *
	 * @throws \DOMException
	 */
	public function normalize (TranslatableContentCollection $data, array $options) : string
	{
		$dom = new \DOMDocument("1.0", "UTF-8");
		$dom->formatOutput = true;

		$xliff = $dom->createElement("xliff");
		$xliff->setAttribute("xmlns", "urn:oasis:names:tc:xliff:document:2.1");
		$xliff->setAttribute("version", "2.1");
		$xliff->setAttribute("srcLang", $data->getLanguage());
		$xliff->setAttribute("trgLang", $options["targetLanguage"] ?? "");
		$dom->appendChild($xliff);

		$file = $dom->createElement("file");
		$file->setAttribute("id", $data->getId());
		$file->setAttribute("original", $data->getUrl());
		$xliff->appendChild($file);

		foreach ($data as $element)
		{
			$unit = $dom->createElement("unit");
			$unit->setAttribute("id", $element->getKey());

			$segment = $dom->createElement("segment");

			$source = $dom->createElement("source");
			$source->appendChild($dom->createCDATASection($element->getValue() ?? ""));

			$target = $dom->createElement("target");

			$segment->appendChild($source);
			$segment->appendChild($target);

			$unit->appendChild($segment);

			$file->appendChild($unit);
		}

		return $dom->saveXML(options: \LIBXML_NOEMPTYTAG) ?: throw new XliffExportException("XLIFF Export failed");
	}

	public function denormalize (string $data) : TranslatableContentCollection
	{
		$xml = new \DOMDocument();

		if (!$xml->loadXML($data))
		{
			throw new XliffInvalidException("XLIFF not valid");
		}

		$xliffNode = $xml->documentElement;

		if (!$xliffNode instanceof \DOMElement)
		{
			throw new XliffInvalidException("Xliff tag missing");
		}

		$fileNode = $xml->getElementsByTagName("file")->item(0);

		if (!$fileNode instanceof \DOMElement)
		{
			throw new XliffInvalidException("File tag missing");
		}

		$unitNodes = $fileNode->getElementsByTagName("unit");

		if (!$unitNodes instanceof \DOMNodeList)
		{
			throw new XliffInvalidException("Tags unit missing");
		}

		$translationDataElements = [];

		foreach ($unitNodes as $unit)
		{
			if (!$unit instanceof \DOMElement)
			{
				continue;
			}

			$targetNode = $unit->getElementsByTagName("target")->item(0);

			if (!$targetNode instanceof \DOMElement)
			{
				throw new XliffInvalidException("Tag target missing in unit");
			}

			$translationDataElements[] = new TranslatableContentElement(
				key: $unit->getAttribute("id"),
				value: $targetNode->textContent,
			);
		}

		return new TranslatableContentCollection(
			id: $fileNode->getAttribute("id"),
			url: $fileNode->getAttribute("original"),
			language: $xliffNode->getAttribute("trgLang"),
			data: $translationDataElements,
		);
	}
}
