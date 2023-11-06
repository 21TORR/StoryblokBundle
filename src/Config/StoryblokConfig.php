<?php declare(strict_types=1);

namespace Torr\Storyblok\Config;

use Torr\Storyblok\Exception\Config\MissingConfigException;

final class StoryblokConfig
{
	/**
	 */
	public function __construct (
		private readonly ?int $spaceId,
		private readonly ?string $managementToken,
		private readonly ?string $contentToken,
		private readonly int $localeLevel,
	) {}

	/**
	 */
	public function getSpaceId () : int
	{
		if (null === $this->spaceId)
		{
			throw new MissingConfigException("No storyblok.space_id configured.");
		}

		return $this->spaceId;
	}

	/**
	 */
	public function getManagementToken () : string
	{
		if (null === $this->managementToken)
		{
			throw new MissingConfigException("No storyblok.management_token configured.");
		}

		return $this->managementToken;
	}

	/**
	 */
	public function getContentToken () : string
	{
		if (null === $this->contentToken)
		{
			throw new MissingConfigException("No storyblok.content_token configured.");
		}

		return $this->contentToken;
	}

	/**
	 */
	public function getStoryblokSpaceUrl () : string
	{
		return \sprintf("https://app.storyblok.com/#/me/spaces/%d/dashboard", $this->getSpaceId());
	}

	/**
	 * Returns the slug level, on which the locales are defined.
	 * 0-based
	 */
	public function getLocaleLevel () : int
	{
		return $this->localeLevel;
	}
}
