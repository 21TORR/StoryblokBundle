<?php declare(strict_types=1);

namespace Torr\Storyblok\RichText;

use Tiptap\Editor;
use Tiptap\Extensions\StarterKit;
use Tiptap\Marks\Highlight;
use Tiptap\Marks\Link;
use Tiptap\Marks\Subscript;
use Tiptap\Marks\Superscript;
use Tiptap\Marks\Underline;
use Tiptap\Nodes\BulletList;
use Tiptap\Nodes\CodeBlock;
use Tiptap\Nodes\HardBreak;
use Tiptap\Nodes\HorizontalRule;
use Tiptap\Nodes\ListItem;
use Tiptap\Nodes\OrderedList;

/**
 * Transformer, that parses HTML and transforms it into rich text structure
 */
final class HtmlToRichTextTransformer
{
	private Editor $editor;

	/**
	 */
	public function __construct ()
	{
		$this->editor = new Editor([
			"extensions" => [
				new StarterKit(),
				new Link(),
				new Highlight([
					"multicolor" => true,
				]),
				new Superscript(),
				new Subscript(),
				new Underline(),
			],
		]);
	}

	/**
	 */
	public function parseHtmlToRichText (?string $html) : ?array
	{
		if (null === $html)
		{
			return null;
		}

		// Fixate names that Storyblok uses for generation
		BulletList::$name = "bullet_list";
		CodeBlock::$name = "code_block";
		HardBreak::$name = "hard_break";
		HorizontalRule::$name = "horizontal_rule";
		ListItem::$name = "list_item";
		OrderedList::$name = "ordered_list";

		return $this->editor
			->setContent($html)
			->getDocument();
	}
}
