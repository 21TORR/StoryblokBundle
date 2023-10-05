<?php declare(strict_types=1);

namespace Torr\Storyblok\Manager\Validator;

use Torr\Cli\Console\Style\TorrStyle;
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


	public function validateDefinitions (TorrStyle $io) : void
	{
		try
		{
			$this->componentNormalizer->normalize($io);
		}
		catch (InvalidComponentConfigurationException|ApiRequestException $exception)
		{
			throw new ValidationFailedException($exception->getMessage(), previous: $exception);
		}
	}
}
