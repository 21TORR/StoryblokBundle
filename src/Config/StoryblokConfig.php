<?php declare(strict_types=1);

namespace Torr\Storyblok\Config;

use Torr\Storyblok\Exception\Config\MissingConfigException;

final readonly class StoryblokConfig
{
	private ?int $spaceId;

	/**
	 */
	public function __construct (
		string $spaceId = "",
		#[\SensitiveParameter]
		private ?string $managementToken = null,
		#[\SensitiveParameter]
		private ?string $contentToken = null,
		private int $localeLevel = 0,
		#[\SensitiveParameter]
		public ?string $webhookSecret = null,
		public bool $allowUrlWebhookSecret = false,
	)
	{
		if ("" === $spaceId)
		{
			$this->spaceId = null;
		}
		elseif (ctype_digit($spaceId))
		{
			$this->spaceId = (int) $spaceId;
		}
		else
		{
			throw new MissingConfigException("Invalid storyblok.space_id configured: must be empty or an integer.");
		}
	}

	/**
	 */
	public function getSpaceId () : int
	{
		return $this->spaceId
			?? throw new MissingConfigException("No storyblok.space_id configured.");
	}

	/**
	 */
	public function getManagementToken () : string
	{
		return $this->managementToken
			?? throw new MissingConfigException("No storyblok.management_token configured.");
	}

	/**
	 */
	public function getContentToken () : string
	{
		return $this->contentToken
			?? throw new MissingConfigException("No storyblok.content_token configured.");
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
