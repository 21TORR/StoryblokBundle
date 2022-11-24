<?php declare(strict_types=1);

namespace Torr\Storyblok\Context;

use Psr\Log\LoggerInterface;
use Torr\Storyblok\Manager\ComponentManager;
use Torr\Storyblok\Transformer\DataTransformer;

final class StoryblokContext
{
	/**
	 */
	public function __construct (
		public readonly ComponentManager $componentManager,
		public readonly DataTransformer $dataTransformer,
		public readonly LoggerInterface $logger,
	) {}
}
