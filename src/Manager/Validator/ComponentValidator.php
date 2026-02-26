<?php declare(strict_types=1);

namespace Torr\Storyblok\Manager\Validator;

use Torr\Storyblok\Adapter\AbstractStoryblokAdapter;
use Torr\Storyblok\Exception\Api\ApiRequestException;
use Torr\Storyblok\Exception\InvalidComponentConfigurationException;
use Torr\Storyblok\Exception\Validation\ValidationFailedException;
use Torr\Storyblok\Manager\ComponentManager;
use Torr\Storyblok\Manager\Normalizer\ComponentNormalizer;

final readonly class ComponentValidator
{
	/**
	 */
	public function __construct (
		private ComponentNormalizer $componentNormalizer,
		private ComponentManager $componentManager,
	) {}

	public function validateComponentsInAdapter (AbstractStoryblokAdapter $adapter) : void
	{
		try
		{
			$components = $this->componentManager->getAllUsedComponentsInAdapter($adapter);
			$this->componentNormalizer->normalize($components, $adapter);
		}
		catch (InvalidComponentConfigurationException|ApiRequestException $exception)
		{
			throw new ValidationFailedException($exception->getMessage(), previous: $exception);
		}
	}
}
