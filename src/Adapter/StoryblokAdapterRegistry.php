<?php declare(strict_types=1);

namespace Torr\Storyblok\Adapter;

use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Torr\Storyblok\Exception\Adapter\UnknownStoryblokAdapterException;

/**
 * @final
 */
readonly class StoryblokAdapterRegistry
{
	public const string DI_TAG = "storyblok.adapter";

	public function __construct (
		/** @var ServiceLocator<AbstractStoryblokAdapter> */
		#[AutowireLocator(services: self::DI_TAG, defaultIndexMethod: 'getKey')]
		private ServiceLocator $storyblokAdapters,
	) {}

	public function getByKey (string $key) : AbstractStoryblokAdapter
	{
		try
		{
			return $this->storyblokAdapters->get($key);
		}
		catch (ServiceNotFoundException $exception)
		{
			throw new UnknownStoryblokAdapterException(
				message: \sprintf(
					"Unknown storyblok adapter: %s",
					$key,
				),
				adapterKey: $key,
				previous: $exception,
			);
		}
	}

	/**
	 * @return AbstractStoryblokAdapter[]
	 */
	public function getAllAdapters () : array
	{
		$storyblokAdapters = array_map(
			fn (string $key) => $this->getByKey($key),
			array_keys($this->storyblokAdapters->getProvidedServices()),
		);

		usort(
			$storyblokAdapters,
			static fn (AbstractStoryblokAdapter $left, AbstractStoryblokAdapter $right) => strnatcmp($left->getDisplayName(), $right->getDisplayName()),
		);

		return $storyblokAdapters;
	}

	/**
	 *
	 */
	public function getByStoryblokSpaceId (string $spaceId) : ?AbstractStoryblokAdapter
	{
		foreach ($this->getAllAdapters() as $adapter)
		{
			if ($adapter->config->getSpaceId() === $spaceId)
			{
				return $adapter;
			}
		}

		return null;
	}
}
