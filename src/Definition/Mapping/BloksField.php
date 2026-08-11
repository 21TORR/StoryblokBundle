<?php declare(strict_types=1);

namespace Torr\Storyblok\Definition\Mapping;

use Torr\Storyblok\Context\ComponentContext;
use Torr\Storyblok\Definition\Data\FieldDefinition;
use Torr\Storyblok\Definition\Field\MappedField;
use Torr\Storyblok\Field\FieldType;
use Torr\Storyblok\Story\Hydrator\StoryHydrator;

/**
 * @final
 */
// #[\Attribute(\Attribute::TARGET_PROPERTY)]
// readonly class BloksField extends MappedField
// {
//	/**
//	 */
//	public function __construct (
//		string $label,
//		?string $key = null,
//	)
//	{
//		parent::__construct($label, $key);
//
//	}
//
//
//	/**
//	 *
//	 */
//	#[\Override]
//	public function getType () : FieldType
//	{
//		return FieldType::Bloks;
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
//	) : mixed
//	{
//		$value = $storyData[$key] ?? null;
//		\assert(is_array($value));
//		$result = [];
//
//		foreach ($value as $nestedBlock)
//		{
//			$result[] = $hydrator->hydrateBlok($nestedBlock);
//		}
//
//		return $result;
//	}
// }
