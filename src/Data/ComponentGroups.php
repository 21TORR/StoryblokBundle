<?php declare(strict_types=1);

namespace Torr\Storyblok\Data;

enum ComponentGroups : string
{
	case PageType = "Page Types";

	case ContentModules = "Content Modules";

	case StructuredData = "Structured Data";

	case StructuredDataElements = "Structured Data Elements";
}
