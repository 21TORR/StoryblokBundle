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

		$document = $this->editor
			->setContent($html)
			->getDocument();

		return $this->wrapListItemInlineContent($document);
	}

	/**
	 * Storyblok requires list items to only contain block nodes (e.g. `paragraph`), but Tiptap puts the inline
	 * content of `<li>Text</li>` directly into the list item. So we wrap all consecutive inline nodes in a paragraph.
	 */
	private function wrapListItemInlineContent (array $node) : array
	{
		if (!isset($node["content"]) || !\is_array($node["content"]))
		{
			return $node;
		}

		$content = array_map(
			fn (mixed $child) => \is_array($child) ? $this->wrapListItemInlineContent($child) : $child,
			$node["content"],
		);

		if ("list_item" === ($node["type"] ?? null))
		{
			$wrapped = [];
			$inline = [];

			foreach ($content as $child)
			{
				if (\is_array($child) && \in_array($child["type"] ?? null, ["text", "hard_break"], true))
				{
					$inline[] = $child;

					continue;
				}

				if ([] !== $inline)
				{
					$wrapped[] = [
						"type" => "paragraph",
						"content" => $inline,
					];

					$inline = [];
				}

				$wrapped[] = $child;
			}

			if ([] !== $inline)
			{
				$wrapped[] = [
					"type" => "paragraph",
					"content" => $inline,
				];
			}

			$content = $wrapped;
		}

		$node["content"] = $content;

		return $node;
	}
}
