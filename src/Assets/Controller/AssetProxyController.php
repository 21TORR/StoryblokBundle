<?php declare(strict_types=1);

namespace Torr\Storyblok\Assets\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Torr\Storyblok\Adapter\StoryblokAdapterRegistry;
use Torr\Storyblok\Assets\Proxy\AssetProxy;
use Torr\Storyblok\Assets\Url\AssetProxyUrlGenerator;

/**
 * @final
 */
class AssetProxyController extends AbstractController
{
	public function proxyAsset (
		AssetProxy $assetProxy,
		StoryblokAdapterRegistry $adapterRegistry,
		AssetProxyUrlGenerator $proxyUrlGenerator,
		Request $request,
		string $spaceId,
		string $path,
	) : Response
	{
		$adapter = $adapterRegistry->getByStoryblokSpaceId($spaceId);

		if (null === $adapter)
		{
			throw $this->createNotFoundException("Adapter for space not found");
		}

		if (!$proxyUrlGenerator->verifyProxyUrlRequest($request))
		{
			throw $this->createNotFoundException("Invalid request");
		}

		$filePath = $assetProxy->getFilePath($adapter, $path);

		if (null === $filePath)
		{
			throw $this->createNotFoundException("File not found");
		}

		return $this->file(
			$filePath,
			disposition: $request->query->has("download")
				? ResponseHeaderBag::DISPOSITION_ATTACHMENT
				: ResponseHeaderBag::DISPOSITION_INLINE,
		);
	}
}
