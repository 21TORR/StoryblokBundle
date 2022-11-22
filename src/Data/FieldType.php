<?php declare(strict_types=1);

namespace Torr\Storyblok\Data;

// A list of all available, non-deprecated built-in Storyblok field types
enum FieldType : string
{
	case Asset = "asset";

	case Bloks = "bloks";

	case Boolean = "boolean";

	case DateTime = "datetime";

	case Link = "link";

	case Markdown = "markdown";

	case MultiAsset = "multiasset";

	case MultiLink = "multilink";

	case Number = "number";

	case Option = "option";

	case Options = "options";

	case RichText = "richtext";

	case Section = "section";

	case Tab = "tab";

	case Table = "table";

	case Text = "text";

	case TextArea = "textarea";
}