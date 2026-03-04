<?php declare(strict_types=1);

namespace Torr\Storyblok\Config;

use Torr\Storyblok\Exception\Config\MissingConfigException;

final readonly class StoryblokConfig
{
	/**
	 */
	public function __construct (
		public string $spaceId,
		#[\SensitiveParameter]
		public string $managementToken,
		#[\SensitiveParameter]
		public string $contentToken,
		public int $localeLevel = 0,
		#[\SensitiveParameter]
		public ?string $webhookSecret = null,
		public bool $allowUrlWebhookSecret = false,
		#[\SensitiveParameter]
		public ?string $assetToken = null,
	)
	{
		if (!ctype_digit($this->spaceId))
		{
			throw new MissingConfigException("Invalid storyblok.space_id configured: must be empty or a string only containing numbers.");
		}
	}

	/**
	 */
	public function getStoryblokSpaceUrl () : string
	{
		return \sprintf("https://app.storyblok.com/#/me/spaces/%d/dashboard", $this->spaceId);
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
