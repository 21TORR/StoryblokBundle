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
use Torr\Storyblok\Tiptap\Helper\FixBrokenLinksMarksHelper;
use Torr\Storyblok\Tiptap\Marks\CustomCodeBlock;
use Torr\Storyblok\Tiptap\Marks\CustomLink;
use Torr\Storyblok\Tiptap\Nodes\CustomListItem;
use Torr\Storyblok\Tiptap\Nodes\CustomOrderedList;

final readonly class RichTextHtmlTransformer
{
	public function __construct (
		private FixBrokenLinksMarksHelper $fixBrokenLinksMarksHelper,
	) {}

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

		return $this->fixBrokenLinksMarksHelper->fixJson($json);
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
}
