<?php declare(strict_types=1);

namespace Torr\Storyblok\Assets\Url;

use Torr\Storyblok\Assets\Url\Data\ParsedStoryblokAssetUrl;

final class StoryblokAssetUrlParser
{
	/**
	 * Parses a Storyblok asset URL into its space id and path.
	 */
	public function parse (string $url) : ?ParsedStoryblokAssetUrl
	{
		if (!preg_match('~^https://a.storyblok.com/f/(?<spaceId>\d+)/(?P<path>.+)$~D', $url, $matches))
		{
			return null;
		}

		return new ParsedStoryblokAssetUrl(
			$matches["spaceId"],
			$matches["path"],
		);
	}
}
