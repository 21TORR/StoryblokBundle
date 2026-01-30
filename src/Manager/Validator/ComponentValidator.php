<?php declare(strict_types=1);

namespace Torr\Storyblok\Manager\Validator;

use Torr\Storyblok\Api\Adapter\AbstractStoryblokAdapter;
use Torr\Storyblok\Exception\Api\ApiRequestException;
use Torr\Storyblok\Exception\InvalidComponentConfigurationException;
use Torr\Storyblok\Exception\Validation\ValidationFailedException;
use Torr\Storyblok\Manager\Normalizer\ComponentNormalizer;

final class ComponentValidator
{
	/**
	 */
	public function __construct (
		private readonly ComponentNormalizer $componentNormalizer,
	) {}

	public function validateDefinitions (AbstractStoryblokAdapter $adapter) : void
	{
		try
		{
			$this->componentNormalizer->normalize($adapter);
		}
		catch (InvalidComponentConfigurationException|ApiRequestException $exception)
		{
			throw new ValidationFailedException($exception->getMessage(), previous: $exception);
		}
	}
}
