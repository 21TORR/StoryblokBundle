<?php declare(strict_types=1);

namespace Torr\Storyblok\Data;

enum RichTextToolbarOption : string
{
	case Bold = "bold";

	case Italic = "italic";

	case Strikethrough = "strike";

	case Components = "blok";

	case underline = "underline";

	case InlineCode = "inlinecode";

	case Code = "code";

	case Paragraph = "paragraph";

	case H1 = "h1";

	case H2 = "h2";

	case H3 = "h3";

	case H4 = "h4";

	case H5 = "h5";

	case H6 = "h6";

	case UnorderedList = "list";

	case OrderedList = "olist";

	case BlockQuote = "quote";

	case HorizontalLine = "hrule";

	case Link = "link";

	case Image = "image";

	case PasteMarkdown = "paste";
}
