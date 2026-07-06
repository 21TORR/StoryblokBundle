<?php declare(strict_types=1);

namespace Torr\Storyblok\Definition\Loader;

use Torr\Storyblok\Attribute\AttributeLoader;
use Torr\Storyblok\Component\Config\ComponentType;
use Torr\Storyblok\Definition\Data\ComponentDefinition;
use Torr\Storyblok\Definition\Exception\InvalidComponentDefinitionException;
use Torr\Storyblok\Definition\Mapping\Blok;
use Torr\Storyblok\Definition\Mapping\Document;
use Torr\Storyblok\Story\Data\BlokStory;
use Torr\Storyblok\Story\Data\DocumentStory;

/**
 * @final
 */
readonly class ComponentDefinitionLoader
{
	/**
	 */
	public function __construct (
		private AttributeLoader $attributeLoader,
	) {}

	/**
	 *
	 */
	public function loadDefinition (string $storyClass) : ?ComponentDefinition
	{
		$blok = $this->attributeLoader->loadBlok($storyClass);
		$document = $this->attributeLoader->loadDocument($storyClass);

		if (null === $blok && null === $document)
		{
			if (\is_a($storyClass, BlokStory::class, true))
			{
				throw new InvalidComponentDefinitionException(\sprintf(
					"Blok component '%s' must have attribute '%s'",
					$storyClass,
					Blok::class,
				));
			}

			if (\is_a($storyClass, DocumentStory::class, true))
			{
				throw new InvalidComponentDefinitionException(\sprintf(
					"Document component '%s' must have attribute '%s'",
					$storyClass,
					Document::class
				));
			}

			return null;
		}

		if (null !== $blok && null !== $document)
		{
			throw new InvalidComponentDefinitionException(\sprintf(
				"Class '%s' can't be both document and blok. Remove one of the attribute.",
				$storyClass,
			));
		}

		return null !== $blok
			? $this->transformBlok($storyClass, $blok)
			: $this->transformDocument($storyClass, $document);
	}

	/**
	 *
	 */
	private function transformBlok (string $storyClass, Blok $blok) : ComponentDefinition
	{
		if (!\is_a($storyClass, BlokStory::class, true))
		{
			throw new InvalidComponentDefinitionException(\sprintf(
				"Blok component '%s' must extend '%s'",
				$storyClass,
				BlokStory::class,
			));
		}

		return new ComponentDefinition(
			label: $blok->label,
			key: $blok->key,
			type: ComponentType::Nested,
		);
	}

	/**
	 *
	 */
	private function transformDocument (string $storyClass, Document $document) : ComponentDefinition
	{
		if (!\is_a($storyClass, DocumentStory::class, true))
		{
			throw new InvalidComponentDefinitionException(\sprintf(
				"Document component '%s' must extend '%s'",
				$storyClass,
				DocumentStory::class,
			));
		}

		return new ComponentDefinition(
			label: $document->label,
			key: $document->key,
			type: ComponentType::Standalone,
		);
	}
}
