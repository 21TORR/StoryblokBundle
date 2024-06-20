<?php declare(strict_types=1);

namespace Torr\Storyblok\Image;

final class ImageDimensionsExtractor
{
	/**
	 * See https://www.storyblok.com/faq/image-dimensions-assets-js
	 *
	 * @return array{int|null, int|null} As [width, height]
	 */
	public function extractImageDimensions (string $imageUrl) : array
	{
		$urlPath = (string) parse_url($imageUrl, \PHP_URL_PATH);
		$urlSegments = explode("/", trim($urlPath, "/"));

		$width = null;
		$height = null;

		if (preg_match('~^(?<width>\\d+)x(?<height>\\d+)$~', $urlSegments[2] ?? "", $matches))
		{
			$width = (int) $matches["width"];
			$height = (int) $matches["height"];
		}

		return [$width, $height];
	}
}
