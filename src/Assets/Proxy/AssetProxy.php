<?php declare(strict_types=1);

namespace Torr\Storyblok\Assets\Proxy;

use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\HttpClient\HttpOptions;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Torr\Storyblok\Adapter\AbstractStoryblokAdapter;

/**
 * @final
 */
readonly class AssetProxy
{
	public function __construct (
		private HttpClientInterface $client,
		private Filesystem $filesystem,
		private string $storagePath,
		private LoggerInterface $logger,
	) {}

	/**
	 * Returns the file path of the proxied file
	 */
	public function getFilePath (
		AbstractStoryblokAdapter $adapter,
		string $path,
	) : ?string
	{
		$targetPath = Path::join($this->storagePath, $path);
		$api = $adapter->contentApi;

		if (!is_file($targetPath))
		{
			$originUrl = \sprintf(
				"https://a.storyblok.com/f/%s/%s?cv=%s",
				$adapter->spaceId,
				ltrim($path, "/"),
				$api->getSpaceInfo()->getCacheVersion(),
			);

			$this->logger->debug("Fetch proxied storyblok asset", [
				"targetPath" => $targetPath,
				"originUrl" => $originUrl,
			]);

			$success = $this->fetchFileFromOrigin($originUrl, $targetPath);

			return $success
				? $targetPath
				: null;
		}

		$this->logger->debug("Found existing proxied storyblok asset", [
			"targetPath" => $targetPath,
		]);

		return $targetPath;
	}

	/**
	 * Fetches a fresh file from the origin
	 *
	 * @return bool whether the file was download correctly
	 */
	private function fetchFileFromOrigin (
		string $originUrl,
		string $targetPath,
	) : bool
	{
		// ensure directory exists
		$this->filesystem->mkdir(\dirname($targetPath));

		// open file handle
		$fileHandle = fopen($targetPath, "wb+");

		if (!$fileHandle)
		{
			$this->logger->critical("Could not create proxied asset file on disk: {targetPath}", [
				"targetPath" => $targetPath,
			]);

			return false;
		}

		try
		{
			// start request
			$response = $this->client->request(
				"GET",
				$originUrl,
				(new HttpOptions())
					->buffer(false)
					->toArray(),
			);

			// stream to file
			foreach ($this->client->stream($response) as $chunk)
			{
				fwrite($fileHandle, $chunk->getContent());
			}

			fclose($fileHandle);

			return true;
		}
		catch (TransportExceptionInterface|HttpExceptionInterface $exception)
		{
			$this->logger->error("Failed to fetch Storyblok asset {originUrl}: {message}", [
				"message" => $exception->getMessage(),
				"originUrl" => $originUrl,
				"targetPath" => $targetPath,
				"exception" => $exception,
			]);

			// be sure to close the file handle and clean up the incomplete file
			fclose($fileHandle);
			$this->filesystem->remove($targetPath);

			return false;
		}
	}

	/**
	 *
	 */
	public function clearCompleteStorage () : void
	{
		$this->filesystem->remove($this->storagePath);
	}

	/**
	 *
	 */
	public function clearStorageFile (string $path) : void
	{
		// directly remove the whole directory
		$dir = \dirname($path);
		$this->filesystem->remove(
			Path::join($this->storagePath, $dir),
		);
	}

	/**
	 *
	 */
	public function getStoragePath () : string
	{
		return $this->storagePath;
	}
}
