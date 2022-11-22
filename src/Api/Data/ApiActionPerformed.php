<?php declare(strict_types=1);

namespace Torr\Storyblok\Api\Data;

enum ApiActionPerformed : string
{
	case ADDED = "added";
	case UPDATED = "updated";
}
