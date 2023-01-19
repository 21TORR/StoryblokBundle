<?php declare(strict_types=1);

namespace Torr\Storyblok\Release;

enum ReleaseVersion : string
{
	case PUBLISHED = "published";

	case DRAFT = "draft";
}
