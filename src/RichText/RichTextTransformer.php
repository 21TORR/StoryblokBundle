<?php declare(strict_types=1);

namespace Torr\Storyblok\RichText;

/**
 * Generic base class to help with traversing the rich text data and rewriting it on-the-fly
 */
abstract class RichTextTransformer
{
	/**
	 * Traverses the RTE content
	 */
	public function transform (array $content) : ?array
	{
		return $this->transformContentOfElement($content);
	}

	/**
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
	 */
	protected function transformParagraph (array $paragraph) : ?array
	{
		return $this->transformContentOfElement($paragraph);
	}

	/**
	 * Transforms a heading
	 *
	 * Structure: array{attrs: array{level: int}, content: array}
	 */
	protected function transformHeading (array $heading) : ?array
	{
		return $this->transformContentOfElement($heading);
	}

	/**
	 * Transforms a bullet (= unordered) list
	 *
	 * Structure: array{content: array}
	 */
	protected function transformBulletList (array $bulletList) : ?array
	{
		return $this->transformContentOfElement($bulletList);
	}

	/**
	 * Transforms an ordered list
	 *
	 * Structure: array{content: array, attrs: array{order: int}}
	 */
	protected function transformOrderedList (array $orderedList) : ?array
	{
		return $this->transformContentOfElement($orderedList);
	}

	/**
	 * Transforms a list item
	 *
	 * Structure: array{content: array}
	 */
	protected function transformListItem (array $listItem) : ?array
	{
		return $this->transformContentOfElement($listItem);
	}

	/**
	 * Transforms a block quote
	 *
	 * Structure: array{content: array}
	 */
	protected function transformBlockQuote (array $blockQuote) : ?array
	{
		return $this->transformContentOfElement($blockQuote);
	}

	/**
	 * Transforms an image
	 *
	 * Structure: array{alt: string, src: string, title: string, copyright: string}
	 */
	protected function transformImage (array $image) : ?array
	{
		return $image;
	}

	/**
	 * Transforms a text
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
	 */
	protected function transformHardBreak (array $hardBreak) : array
	{
		return $hardBreak;
	}

	/**
	 * Transforms a code block
	 *
	 *  Structure: array{attrs: array{class: string}, content: array}
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
	 */
	protected function transformHorizontalRule (array $horizontalRule) : array
	{
		return $horizontalRule;
	}

	/**
	 * Transforms an emoji
	 *
	 *  Structure: array{attrs{name: string, emoji: string, fallbackImage: string}}
	 */
	protected function transformEmoji (array $emoji) : array
	{
		return $emoji;
	}
	//endregion


	// region Mark Transformers
	/**
	 * Structure: array{type: string, attrs: array{href: string, uuid: string, anchor: string|null, custom: array, target: string, linktype: string}}
	 */
	protected function transformLinkMark (array $linkMark) : ?array
	{
		return $linkMark;
	}
	// endregion


	/**
	 * Structure: array{type: string, attrs: array{href: string, uuid: string, anchor: string|null, custom: array, target: string, linktype: string}}
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
	 *
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
