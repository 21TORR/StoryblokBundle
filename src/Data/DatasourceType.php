<?php declare(strict_types=1);

namespace Torr\Storyblok\Data;

enum DatasourceType : string
{
	case self = "undefined";

	case Datasource = "internal";

	case Stories = "internal_stories";

	case ExternalApi = "external";

	case Languages = "internal_languages";
}
