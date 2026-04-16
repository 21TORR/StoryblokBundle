<?php declare(strict_types=1);

namespace Torr\Storyblok\Assets\Url;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @final
 */
readonly class AssetProxyUrlGenerator
{
	/**
	 */
	public function __construct (
		private UrlGeneratorInterface $urlGenerator,
		private UriSigner $uriSigner,
		private StoryblokAssetUrlParser $assetUrlParser,
	) {}

	/**
	 * Rewrites a Storyblok asset URL to a proxied one
	 */
	public function rewriteAssetUrl (string $storyblokUrl) : string
	{
		$parsedAssetUrl = $this->assetUrlParser->parse($storyblokUrl);

		// if it's not a Storyblok asset URL, just return
		if (null === $parsedAssetUrl)
		{
			return $storyblokUrl;
		}

		$url = $this->urlGenerator->generate("storyblok.asset-proxy", [
			"spaceId" => $parsedAssetUrl->spaceId,
			"path" => $parsedAssetUrl->path,
		], UrlGeneratorInterface::ABSOLUTE_URL);

		return $this->uriSigner->sign($url, null);
	}

	/**
	 */
	public function verifyProxyUrlRequest (Request $request) : bool
	{
		// strip other query parameters, we only care about the _hash
		$urlToCheck = \sprintf(
			"%s?%s",
			$request->getUriForPath($request->getPathInfo()),
			http_build_query([
				"_hash" => $request->query->get("_hash"),
			]),
		);

		return $this->uriSigner->check($urlToCheck);
	}
}
