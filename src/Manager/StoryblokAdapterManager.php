<?php declare(strict_types=1);

namespace Torr\Storyblok\Manager;

use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Torr\Storyblok\Api\Adapter\AbstractStoryblokAdapter;
use Torr\Storyblok\Exception\Adapter\UnknownAdapterKeyException;

class StoryblokAdapterManager
{
	/**
	 */
	public function __construct (
		/** @var ServiceLocator<AbstractStoryblokAdapter> */
		#[AutowireLocator(services: 'storyblok.adapter.definition', defaultIndexMethod: 'getKey')]
		private readonly ServiceLocator $adapters,
	) {}

	/**
	 * @return list<AbstractStoryblokAdapter>
	 */
	public function getAllAdapters () : array
	{
		return array_map(
			fn (string $key) => $this->getAdapter($key),
			array_keys($this->adapters->getProvidedServices()),
		);
	}

	/**
	 * Gets the adapter by key
	 *
	 * @throws UnknownAdapterKeyException
	 */
	public function getAdapter (string $key) : AbstractStoryblokAdapter
	{
		try
		{
			$adapter = $this->adapters->get($key);
			\assert($adapter instanceof AbstractStoryblokAdapter);

			return $adapter;
		}
		catch (ServiceNotFoundException $exception)
		{
			throw new UnknownAdapterKeyException(
				message: \sprintf(
					"Unknown adapter type: %s",
					$key,
				),
				adapterKey: $key,
				previous: $exception,
			);
		}
	}
}
