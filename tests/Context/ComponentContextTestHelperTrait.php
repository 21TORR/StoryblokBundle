<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Context;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Torr\Storyblok\Context\ComponentContext;
use Torr\Storyblok\Image\ImageDimensionsExtractor;
use Torr\Storyblok\Manager\ComponentManager;
use Torr\Storyblok\Transformer\DataTransformer;
use Torr\Storyblok\Validator\DataValidator;

trait ComponentContextTestHelperTrait
{
	/**
	 */
	private function createDummyContext (
		?ComponentManager $componentManager = null,
		?DataTransformer $dataTransformer = null,
		?LoggerInterface $logger = null,
		?DataValidator $validator = null,
		?ImageDimensionsExtractor $imageDimensionsExtractor = null,
	) : ComponentContext
	{
		return new ComponentContext(
			$componentManager ?? $this->createMock(ComponentManager::class),
			$dataTransformer ?? new DataTransformer(),
			$logger ?? new NullLogger(),
			$validator ?? $this->createMock(DataValidator::class),
			$imageDimensionsExtractor ?? new ImageDimensionsExtractor(),
		);
	}
}
