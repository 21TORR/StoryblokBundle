<?php declare(strict_types=1);

namespace Torr\Storyblok\Context;

use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Contracts\Service\Attribute\Required;
use Torr\Storyblok\Api\Transformer\StoryblokIdSlugMapper;
use Torr\Storyblok\Component\AbstractComponent;
use Torr\Storyblok\Field\FieldDefinitionInterface;
use Torr\Storyblok\Image\ImageDimensionsExtractor;
use Torr\Storyblok\Manager\ComponentManager;
use Torr\Storyblok\Transformer\DataTransformer;
use Torr\Storyblok\Validator\DataValidator;

/**
 * @final
 */
class ComponentContext
{
	public ?StoryblokIdSlugMapper $storyblokIdSlugMapper = null;

	/**
	 */
	public function __construct (
		public readonly ComponentManager $componentManager,
		public readonly DataTransformer $dataTransformer,
		public readonly LoggerInterface $logger,
		public readonly DataValidator $validator,
		public readonly ImageDimensionsExtractor $imageDimensionsExtractor,
	) {}

	/**
	 * This setter is only required, as we need to break a circular service definition.
	 *
	 * @internal
	 */
	#[Required]
	public function setStoryblokIdSlugMapper (StoryblokIdSlugMapper $storyblokIdSlugMapper) : void
	{
		$this->storyblokIdSlugMapper = $storyblokIdSlugMapper;
	}

	/**
	 * @see DataValidator::ensureDataIsValid()
	 *
	 * @param string[]               $contentPath
	 * @param array<Constraint|null> $constraints
	 */
	public function ensureDataIsValid (
		array $contentPath,
		FieldDefinitionInterface $field,
		mixed $data,
		array $constraints,
	) : void
	{
		$this->validator->ensureDataIsValid($contentPath, $field, $data, $constraints);
	}

	/**
	 * @see ComponentManager::getComponent()
	 */
	public function getComponentByKey (string $key) : AbstractComponent
	{
		return $this->componentManager->getComponent($key);
	}

	/**
	 * @see DataTransformer::normalizeOptionalString()
	 */
	public function normalizeOptionalString (?string $value) : ?string
	{
		return $this->dataTransformer->normalizeOptionalString($value);
	}

	/**
	 * @return array{int|null, int|null}
	 */
	public function extractImageDimensions (string $imageUrl) : array
	{
		return $this->imageDimensionsExtractor->extractImageDimensions($imageUrl);
	}

	/**
	 * @see StoryblokIdSlugMapper::getFullSlugById()
	 */
	public function fetchFullSlugByUuid (string|int $identifier) : string|null
	{
		// the content api is already set by the DI container
		\assert(null !== $this->storyblokIdSlugMapper);
		return $this->storyblokIdSlugMapper->getFullSlugById($identifier);
	}
}
