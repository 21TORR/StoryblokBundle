<?php declare(strict_types=1);

namespace Torr\Storyblok\Tiptap\Transformer;

use Tiptap\Editor;
use Tiptap\Extensions\StarterKit;
use Tiptap\Marks\Highlight;
use Tiptap\Marks\Subscript;
use Tiptap\Marks\Superscript;
use Tiptap\Marks\Underline;
use Tiptap\Nodes\BulletList;
use Tiptap\Nodes\CodeBlock;
use Tiptap\Nodes\HardBreak;
use Tiptap\Nodes\HorizontalRule;
use Tiptap\Nodes\ListItem;
use Tiptap\Nodes\OrderedList;
use Torr\Storyblok\Tiptap\Marks\CustomCodeBlock;
use Torr\Storyblok\Tiptap\Marks\CustomLink;
use Torr\Storyblok\Tiptap\Nodes\CustomListItem;
use Torr\Storyblok\Tiptap\Nodes\CustomOrderedList;

final class RichTextHtmlTransformer
{
	public function transformToHtml (string $jsonMarkup) : string
	{
		return $this->createEditor()
			->setContent($jsonMarkup)
			->getHTML();
	}

	public function transformToJsonMarkup (string $html) : string
	{
		$json = $this->createEditor()
			->setContent($html)
			->getJSON();

		return $this->fixBrokenLinkMarks($json);
	}

	public function transformToPlainText (string $jsonMarkup) : string
	{
		return $this->createEditor()
			->setContent($jsonMarkup)
			->getText();
	}

	private function createEditor () : Editor
	{
		BulletList::$name = "bullet_list";
		CodeBlock::$name = "code_block";
		HardBreak::$name = "hard_break";
		HorizontalRule::$name = "horizontal_rule";
		ListItem::$name = "list_item";
		OrderedList::$name = "ordered_list";

		return new Editor([
			"extensions" => [
				new StarterKit([
					"codeBlock" => false,
					"orderedList" => false,
					"listItem" => false,
				]),
				new CustomListItem(),
				new CustomLink(),
				new Highlight([
					"multicolor" => true,
				]),
				new Superscript(),
				new Subscript(),
				new Underline(),
				new CustomCodeBlock(),
				new CustomOrderedList(),
			],
		]);
	}

	private function fixBrokenLinkMarks (string $rawJson) : string
	{
		try
		{
			$json = json_decode($rawJson, true, 512, \JSON_THROW_ON_ERROR);

			if (!\is_array($json))
			{
				return $rawJson;
			}

			$modifiedJson = [];
			$nodeContent = $json["content"] ?? [];

			foreach ($nodeContent as $key => $item)
			{
				$modifiedJson[$key] = $this->traverseNode($item);
			}

			return json_encode([
				...$json,
				"content" => $modifiedJson,
			], \JSON_THROW_ON_ERROR);
		}
		catch (\JsonException $e)
		{
			return $rawJson;
		}
	}

	private function traverseNode (array $node) : array
	{
		$content = $node["content"] ?? null;
		$marks = $node["marks"] ?? null;

		if (\is_array($content))
		{
			$modified = [];

			foreach ($content as $key => $childNode)
			{
				$modified[$key] = $this->traverseNode($childNode);
			}

			return [
				...$node,
				"content" => $modified,
			];
		}

		if (\is_array($marks))
		{
			$modified = [];

			foreach ($marks as $key => $mark)
			{
				$modified[$key] = $this->traverseMark($mark);
			}

			return [
				...$node,
				"marks" => $modified,
			];
		}

		return $node;
	}

	private function traverseMark (array $mark) : array
	{
		switch ($mark["type"] ?? null)
		{
			case "link":
				$linkType = $mark["attrs"]["linktype"] ?? null;

				return match($linkType)
				{
					"story",
					"email",
					"url",
					"asset" => [
						...$mark,
						"attrs" => [
							"uuid" => null,
							"anchor" => null,
							...$mark["attrs"],
						],
					],

					default => $mark,
				};

			default:
				return $mark;
		}
	}
}
