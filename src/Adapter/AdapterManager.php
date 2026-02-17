<?php declare(strict_types=1);

namespace Torr\Storyblok\Adapter;

use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Torr\Storyblok\Component\AbstractComponent;

/**
 * @final
 */
readonly class AdapterManager
{
	public const string DI_TAG = "storyblok.adapter";

	/**
	 */
	public function __construct (
		/** @var ServiceLocator<AbstractStoryblokAdapter> */
		#[AutowireLocator(services: self::DI_TAG, defaultIndexMethod: 'getKey')]
		private ServiceLocator $adapters,
	) {}

	/**
	 *
	 */
	public function getByKey (string $key) : AbstractStoryblokAdapter
	{
		return $this->adapters->get($key);
	}

	/**
	 * @return AbstractStoryblokAdapter[]
	 */
	public function getAllAdapters () : array
	{
		$availableKeys = array_keys($this->adapters->getProvidedServices());
		$result = [];

		foreach ($availableKeys as $key)
		{
			$result[] = $this->getByKey($key);
		}

		return $result;
	}
}
