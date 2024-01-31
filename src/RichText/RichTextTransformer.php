<?php declare(strict_types=1);

namespace Torr\Storyblok\RichText;

/**
 * Generic base class to help with traversing the rich text data and rewriting it on-the-fly.
 *
 * @phpstan-type RichTextDocument array{type: "doc", content: RichTextContent[]}
 * @phpstan-type RichTextContent Blockquote|BulletList|Heading|Image|ListItem|OrderedList|Paragraph|Text|HardBreak|CodeBlock|HorizontalRule|Emoji
 *
 * @phpstan-type Blockquote array{type: "blockquote", content: Paragraph[]}
 * @phpstan-type BulletList array{type: "bullet_list", content: ListItem[]}
 * @phpstan-type Heading array{type: "heading", content: RichTextContent[], attrs: array{level: int}}
 * @phpstan-type Image array{type: "image", alt: string, src: string, title: string, copyright: string}
 * @phpstan-type ListItem array{type: "list_item", content: Paragraph[]}
 * @phpstan-type OrderedList array{type: "ordered_list", content: ListItem[], attrs: array{order: int}}
 * @phpstan-type Paragraph array{type: "paragraph", content: RichTextContent[]}
 * @phpstan-type Text array{type: "text", text: string, marks: Mark[]}
 * @phpstan-type HardBreak array{type: "hard_break"}
 * @phpstan-type CodeBlock array{type: "code_block", content: Text[], attrs: array{class: string}}
 * @phpstan-type HorizontalRule array{type: "horizontal_rule"}
 * @phpstan-type Emoji array{type: "emoji", attrs: array{name: string, emoji: string, fallbackImage: string}}
 *
 * @phpstan-type Mark MarkBold|MarkItalic|MarkStrike|MarkUnderline|MarkCode|MarkLink|MarkStyled|MarkSubScript|MarkSuperScript|MarkHighlight|MarkTextStyle|MarkAnchor
 * @phpstan-type MarkBold array{type: "bold"}
 * @phpstan-type MarkItalic array{type: "italic"}
 * @phpstan-type MarkStrike array{type: "strike"}
 * @phpstan-type MarkUnderline array{type: "underline"}
 * @phpstan-type MarkCode array{type: "code"}
 * @phpstan-type MarkLink array{type: "link", attrs: array{href: string, uuid: string|null, anchor: string|null, custom: array, target: string, linktype: string}}
 * @phpstan-type MarkStyled array{type: "styled", attrs: array{class: string}}
 * @phpstan-type MarkSubScript array{type: "subscript"}
 * @phpstan-type MarkSuperScript array{type: "superscript"}
 * @phpstan-type MarkHighlight array{type: "highlight", attrs: array{color: string}}
 * @phpstan-type MarkTextStyle array{type: "textStyle", attrs: array{color: string}}
 * @phpstan-type MarkAnchor array{type: "anchor", attrs: array{id: string}}
 */
abstract class RichTextTransformer
{
	/**
	 * Traverses the RTE content
	 *
	 * @param RichTextDocument $content
	 */
	public function transform (array $content) : ?array
	{
		return $this->transformContentOfElement($content);
	}

	/**
	 * @param RichTextContent $element
	 */
	protected function transformContentElement (array $element) : ?array
	{
		return match ($element["type"])
		{
			"blockquote" => $this->transformBlockQuote($element),
			"bullet_list" => $this->transformBulletList($element),
			"heading" => $this->transformHeading($element),
			"image" => $this->transformImage($element),
			"list_item" => $this->transformListItem($element),
			"ordered_list" => $this->transformOrderedList($element),
			"paragraph" => $this->transformParagraph($element),
			"text" => $this->transformText($element),
			"hard_break" => $this->transformHardBreak($element),
			"code_block" => $this->transformCodeBlock($element),
			"horizontal_rule" => $this->transformHorizontalRule($element),
			"emoji" => $this->transformEmoji($element),
			default => $element,
		};
	}

	//region Content Element Transformers
	/**
	 * Transforms a paragraph
	 *
	 * Structure: array{"text": string, "marks": array<Mark>} (see transformMarks())
	 *
	 * @param Paragraph $paragraph
	 */
	protected function transformParagraph (array $paragraph) : ?array
	{
		return $this->transformContentOfElement($paragraph);
	}

	/**
	 * Transforms a heading
	 *
	 * Structure: array{attrs: array{level: int}, content: array}
	 *
	 * @param Heading $heading
	 */
	protected function transformHeading (array $heading) : ?array
	{
		return $this->transformContentOfElement($heading);
	}

	/**
	 * Transforms a bullet (= unordered) list
	 *
	 * Structure: array{content: array}
	 *
	 * @param BulletList $bulletList
	 */
	protected function transformBulletList (array $bulletList) : ?array
	{
		return $this->transformContentOfElement($bulletList);
	}

	/**
	 * Transforms an ordered list
	 *
	 * Structure: array{content: array, attrs: array{order: int}}
	 *
	 * @param OrderedList $orderedList
	 */
	protected function transformOrderedList (array $orderedList) : ?array
	{
		return $this->transformContentOfElement($orderedList);
	}

	/**
	 * Transforms a list item
	 *
	 * Structure: array{content: array}
	 *
	 * @param ListItem $listItem
	 */
	protected function transformListItem (array $listItem) : ?array
	{
		return $this->transformContentOfElement($listItem);
	}

	/**
	 * Transforms a block quote
	 *
	 * Structure: array{content: array}
	 *
	 * @param Blockquote $blockQuote
	 */
	protected function transformBlockQuote (array $blockQuote) : ?array
	{
		return $this->transformContentOfElement($blockQuote);
	}

	/**
	 * Transforms an image
	 *
	 * Structure: array{alt: string, src: string, title: string, copyright: string}
	 *
	 * @param Image $image
	 */
	protected function transformImage (array $image) : ?array
	{
		return $image;
	}

	/**
	 * Transforms a text
	 *
	 * @param Text $text
	 */
	protected function transformText (array $text) : ?array
	{
		if (!isset($text["marks"]))
		{
			return $text;
		}

		$transformedMarks = [];

		foreach ($text["marks"] as $mark)
		{
			$transformed = $this->transformMark($mark);

			if (null !== $transformed)
			{
				$transformedMarks[] = $transformed;
			}
		}

		return [
			...$text,
			"marks" => $transformedMarks,
		];
	}

	/**
	 * Transforms a hard break (SHIFT+Enter)
	 *
	 *  Structure: array{}
	 *  Technically, the structure just contains the `type` key as a line break doesn't have any content in itself.
	 *
	 * @param HardBreak $hardBreak
	 */
	protected function transformHardBreak (array $hardBreak) : array
	{
		return $hardBreak;
	}

	/**
	 * Transforms a code block
	 *
	 *  Structure: array{attrs: array{class: string}, content: array}
	 *
	 * @param CodeBlock $codeBlock
	 */
	protected function transformCodeBlock (array $codeBlock) : array
	{
		return $codeBlock;
	}

	/**
	 * Transforms a horizontal rule
	 *
	 *  Structure: array{}
	 *  Technically, the structure just contains the `type` key as a horizontal rule doesn't have any content in itself.
	 *
	 * @param HorizontalRule $horizontalRule
	 */
	protected function transformHorizontalRule (array $horizontalRule) : array
	{
		return $horizontalRule;
	}

	/**
	 * Transforms an emoji
	 *
	 *  Structure: array{attrs{name: string, emoji: string, fallbackImage: string}}
	 *
	 * @param Emoji $emoji
	 */
	protected function transformEmoji (array $emoji) : array
	{
		return $emoji;
	}
	//endregion


	// region Mark Transformers
	/**
	 * Structure: array{type: string, attrs: array{href: string, uuid: string, anchor: string|null, custom: array, target: string, linktype: string}}
	 *
	 * @param MarkLink $linkMark
	 */
	protected function transformLinkMark (array $linkMark) : ?array
	{
		return $linkMark;
	}
	// endregion


	/**
	 * Structure: array{type: string, attrs: array{href: string, uuid: string, anchor: string|null, custom: array, target: string, linktype: string}}
	 *
	 * @param Mark $mark
	 */
	private function transformMark (array $mark) : ?array
	{
		return match ($mark["type"])
		{
			"link" => $this->transformLinkMark($mark),
			default => $mark,
		};
	}

	/**
	 * @param RichTextContent $element
	 */
	private function transformContentOfElement (array $element) : ?array
	{
		if (!isset($element["content"]))
		{
			return $element;
		}

		$transformedElements = [];

		foreach ($element["content"] as $contentElement)
		{
			$transformed = $this->transformContentElement($contentElement);

			if (null !== $transformed)
			{
				$transformedElements[] = $transformed;
			}
		}

		if (empty($transformedElements))
		{
			return null;
		}

		return [
			...$element,
			"content" => $transformedElements,
		];
	}
}
