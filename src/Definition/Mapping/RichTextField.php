<?php declare(strict_types=1);

namespace Torr\Storyblok\Definition\Mapping;

use Torr\Storyblok\Content\RichText;
use Torr\Storyblok\Context\ComponentContext;
use Torr\Storyblok\Definition\Data\FieldDefinition;
use Torr\Storyblok\Definition\Field\MappedField;
use Torr\Storyblok\Field\FieldType;
use Torr\Storyblok\Story\Hydrator\StoryHydrator;

/**
 * @final
 */
// #[\Attribute(\Attribute::TARGET_PROPERTY)]
// readonly class RichTextField extends MappedField
// {
//	/**
//	 *
//	 */
//	#[\Override]
//	public function getType () : FieldType
//	{
//		return FieldType::RichText;
//	}
//
//	/**
//	 *
//	 */
//	#[\Override]
//	public function getManagementApiData () : array
//	{
//		return array_replace(
//			parent::getManagementApiData(),
//			[
//			],
//		);
//	}
//
//	/**
//	 *
//	 */
//	#[\Override]
//	public function transformStoryblokValue (
//		string $key,
//		array $storyData,
//		FieldDefinition $definition,
//		ComponentContext $context,
//		StoryHydrator $hydrator,
//	) : ?RichText
//	{
//		$value = $storyData[$key] ?? null;
//		\assert(null === $value || \is_array($value));
//
//		return null !== $value
//			? new RichText($value)
//			: null;
//	}
// }
