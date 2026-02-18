<?php declare(strict_types=1);

namespace Torr\Storyblok\Adapter;

use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Torr\Storyblok\Exception\Adapter\UnknownAdapterKeyException;

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
			$storyblokAdapter = $this->storyblokAdapters->get($key);

			return $storyblokAdapter;
		}
		catch (ServiceNotFoundException $exception)
		{
			throw new UnknownAdapterKeyException(
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
}
