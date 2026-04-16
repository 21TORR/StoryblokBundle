<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Assets\Url;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Torr\Storyblok\Assets\Url\AssetProxyUrlGenerator;
use Torr\Storyblok\Assets\Url\StoryblokAssetUrlParser;

/**
 * @internal
 */
final class AssetProxyUrlGeneratorTest extends TestCase
{
	/**
	 */
	public function testRewriteAssetUrlRewritesAndSignsStoryblokUrl () : void
	{
		$urlGenerator = $this->createMock(UrlGeneratorInterface::class);
		$urlGenerator->expects(self::once())
			->method("generate")
			->with(
				"storyblok.asset-proxy",
				[
					"spaceId" => "123",
					"path" => "folder/image.jpg",
				],
				UrlGeneratorInterface::ABSOLUTE_URL,
			)
			->willReturn("https://example.com/asset/123/folder/image.jpg");

		$uriSigner = new UriSigner("test-secret");
		$generator = new AssetProxyUrlGenerator(
			$urlGenerator,
			$uriSigner,
			new StoryblokAssetUrlParser(),
		);

		$rewrittenUrl = $generator->rewriteAssetUrl("https://a.storyblok.com/f/123/folder/image.jpg");

		self::assertSame(
			$uriSigner->sign("https://example.com/asset/123/folder/image.jpg", null),
			$rewrittenUrl,
		);
	}

	/**
	 */
	public function testRewriteAssetUrlLeavesNonStoryblokUrlUntouched () : void
	{
		$urlGenerator = $this->createMock(UrlGeneratorInterface::class);
		$urlGenerator->expects(self::never())
			->method("generate");

		$generator = new AssetProxyUrlGenerator(
			$urlGenerator,
			new UriSigner("test-secret"),
			new StoryblokAssetUrlParser(),
		);

		self::assertSame(
			"https://example.com/image.jpg",
			$generator->rewriteAssetUrl("https://example.com/image.jpg"),
		);
	}
}
