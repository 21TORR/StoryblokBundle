<?php declare(strict_types=1);

namespace Torr\Storyblok\Assets;

use Symfony\Component\Asset\Package;
use Symfony\Component\Asset\VersionStrategy\StaticVersionStrategy;
use Symfony\Component\HttpFoundation\UrlHelper;

final readonly class ComponentAssetsHelper
{
	public function __construct (
		private UrlHelper $urlHelper,
	) {}

	public function generatePreviewScreenshotUrl (string $subPath) : string
	{
		$cacheDate = new \DateTimeImmutable();
		$package = new Package(new StaticVersionStrategy($cacheDate->format("U"), "%s?version=%s"));

		return $this->urlHelper->getAbsoluteUrl($package->getUrl($subPath));
	}
}
