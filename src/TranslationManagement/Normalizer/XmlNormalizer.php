<?php declare(strict_types=1);

namespace Torr\Storyblok\TranslationManagement\Normalizer;

use Torr\Storyblok\TranslationManagement\Data\TranslatableContentCollection;
use Torr\Storyblok\TranslationManagement\Data\TranslatableContentElement;
use Torr\Storyblok\TranslationManagement\Exception\XmlExportException;
use Torr\Storyblok\TranslationManagement\Exception\XmlInvalidException;

final readonly class XmlNormalizer
{
	public function normalize (TranslatableContentCollection $data) : string
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

	public function denormalize (string $data) : TranslatableContentCollection
	{
		$xml = new \DOMDocument();

		if (!$xml->loadXML($data))
		{
			throw new XmlInvalidException("XML not valid");
		}

		$xpath = new \DOMXPath($xml);

		$page = $xml->documentElement ?? throw new XmlInvalidException("XML root element missing");

		$nameNodes = $xpath->query(\sprintf("/%s/name", $page->tagName));
		$nameNode = $nameNodes[0] ?? null;

		if (!$nameNode instanceof \DOMElement)
		{
			throw new XmlInvalidException("XML not valid: Name node missing");
		}

		if (
			"" === $page->getAttribute("id")
			|| "" === $page->getAttribute("filename")
			|| "" === $page->getAttribute("url")
			|| "" === $page->getAttribute("language")
		)
		{
			throw new XmlInvalidException("XML not valid: Page node invalid");
		}

		$tagNodes = $xpath->query("//tag");

		if (!$tagNodes instanceof \DOMNodeList)
		{
			throw new XmlInvalidException("XML not valid");
		}

		$translationDataElements = [];

		foreach ($tagNodes as $tagNode)
		{
			if (!$tagNode instanceof \DOMElement)
			{
				continue;
			}

			$textNode = $tagNode->getElementsByTagName("text")->item(0);

			if (null === $textNode)
			{
				throw new XmlInvalidException("XML not valid: text node missing");
			}

			if (
				"" === $tagNode->getAttribute("id")
				|| "" === $tagNode->getAttribute("type")
			)
			{
				throw new XmlInvalidException("XML not valid: Tag node invalid");
			}

			$translationDataElements[] = new TranslatableContentElement(
				key: $tagNode->getAttribute("id"),
				type: $tagNode->getAttribute("type"),
				value: $textNode->textContent,
			);
		}

		return new TranslatableContentCollection(
			id: $page->getAttribute("id"),
			filename: $page->getAttribute("filename"),
			url: $page->getAttribute("url"),
			language: $page->getAttribute("language"),
			name: $nameNode->textContent,
			data: $translationDataElements,
		);
	}
}
