<?php declare(strict_types=1);

namespace Torr\Storyblok\Assets\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Torr\Storyblok\Assets\Proxy\AssetProxy;
use Torr\Storyblok\Assets\Url\AssetProxyUrlGenerator;

/**
 * @final
 */
class AssetProxyController extends AbstractController
{
	public function proxyAsset (
		AssetProxy $assetProxy,
		AssetProxyUrlGenerator $proxyUrlGenerator,
		Request $request,
		string $path,
	) : Response
	{
		if (!$proxyUrlGenerator->verifyProxyUrlRequest($request))
		{
			throw $this->createNotFoundException("Invalid request");
		}

		$filePath = $assetProxy->getFilePath($path);

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
