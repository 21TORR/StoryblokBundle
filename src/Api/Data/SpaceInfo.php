<?php declare(strict_types=1);

namespace Torr\Storyblok\Api\Data;

final class SpaceInfo
{
	private readonly string $spaceId;
	private readonly string $name;
	private readonly int $cacheVersion;

	/** @var list<string> */
	private readonly array $languageCodes;
	private readonly string $domain;

	/**
	 */
	public function __construct (array $data)
	{
		$this->spaceId = (string) $data["id"];
		$this->name = $data["name"];
		$this->cacheVersion = $data["version"];
		$this->languageCodes = $data["language_codes"];
		$this->domain = $data["domain"];
	}

	/**
	 */
	public function getId () : string
	{
		return $this->spaceId;
	}

	/**
	 */
	public function getName () : string
	{
		return $this->name;
	}

	/**
	 */
	public function getCacheVersion () : int
	{
		return $this->cacheVersion;
	}

	/**
	 */
	public function getLanguageCodes () : array
	{
		return $this->languageCodes;
	}

	/**
	 */
	public function getDomain () : string
	{
		return $this->domain;
	}

	/**
	 */
	public function getBackendDashboardUrl () : string
	{
		return \sprintf("https://app.storyblok.com/#/me/spaces/%d/dashboard", $this->spaceId);
	}
}
