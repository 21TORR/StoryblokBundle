<?php declare(strict_types=1);

namespace Torr\Storyblok\Api;

use Symfony\Component\HttpClient\HttpOptions;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Torr\Storyblok\Api\Data\ApiActionPerformed;
use Torr\Storyblok\Api\Data\ComponentIdMap;
use Torr\Storyblok\Config\StoryblokConfig;
use Torr\Storyblok\Exception\Api\ApiRequestFailedException;

final class ManagementApi
{
	private const API_URL = "https://mapi.storyblok.com/v1/spaces/%d/";
	private readonly HttpClientInterface $client;
	private ?ComponentIdMap $componentIdMap = null;

	/**
	 */
	public function __construct (
		private readonly StoryblokConfig $config,
		HttpClientInterface $client,
	)
	{
		$this->client = $client->withOptions(
			(new HttpOptions())
				->setBaseUri(\sprintf(self::API_URL, $this->config->getSpaceId()))
				->toArray(),
		);
	}

	/**
	 */
	public function syncComponent (array $config) : ApiActionPerformed
	{
		$componentIdMap = $this->getComponentMap();

		try
		{
			$options = (new HttpOptions())
				->setHeaders([
					"Authorization" => $this->config->getManagementToken(),
				])
				->setJson([
					"component" => $config,
				])
				->toArray();
			$componentId = $this->getComponentMap()->getComponentId($config["name"]);

			$response = null !== $componentId
				? $this->client->request("PUT", "components/{$componentId}", $options)
				: $this->client->request("POST", "components", $options);

			// add id to component id map
			$data = $response->toArray();
			$componentIdMap->registerComponent($data["component"]["name"], $data["component"]["id"]);

			return null !== $componentId
				? ApiActionPerformed::UPDATED
				: ApiActionPerformed::ADDED;
		}
		catch (ExceptionInterface $e)
		{
			throw new ApiRequestFailedException(\sprintf(
				"Management API request failed: %s",
				$e->getMessage(),
			), previous: $e);
		}
	}

	/**
	 *
	 */
	private function getComponentMap () : ComponentIdMap
	{
		if (null === $this->componentIdMap)
		{
			$this->componentIdMap = $this->fetchComponentIdMap();
		}

		return $this->componentIdMap;
	}

	/**
	 * Fetches the component ID mapping
	 */
	private function fetchComponentIdMap () : ComponentIdMap
	{
		try
		{
			$response = $this->client->request(
				"GET",
				"components",
				(new HttpOptions())
					->setHeaders([
						"Authorization" => $this->config->getManagementToken(),
					])
					->toArray(),
			);

			return new ComponentIdMap($response->toArray());
		}
		catch (ExceptionInterface $e)
		{
			throw new ApiRequestFailedException(\sprintf(
				"Failed to fetch existing components: %s",
				$e->getMessage(),
			), previous: $e);
		}
	}
}
