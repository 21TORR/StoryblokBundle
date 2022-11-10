<?php declare(strict_types=1);

namespace Torr\Storyblok\Data;

enum StoryblokDatasourceType : string
{
	case self = "undefined";

	case Datasource = "internal";

	case Stories = "internal_stories";

	case ExternalApi = "external";

	case Languages = "internal_languages";
}
