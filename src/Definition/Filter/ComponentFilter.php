<?php declare(strict_types=1);

namespace Torr\Storyblok\Definition\Filter;

use Torr\Storyblok\Definition\Exception\Filter\InvalidComponentFilterException;
use Torr\Storyblok\Helper\EnumHelper;

final readonly class ComponentFilter
{
	/**
	 */
	private function __construct (
		/** @var list<string|\BackedEnum> */
		public array $tags = [],
		/** @var list<string|\BackedEnum> */
		public array $components = [],
	) {}

	/**
	 */
	public static function tags (string|\BackedEnum $tag, string|\BackedEnum ...$additionalTags) : self
	{
		return new self(
			tags: self::transformValues([$tag, ...$additionalTags]),
		);
	}

	/**
	 */
	public static function keys (string|\BackedEnum $component, string|\BackedEnum ...$additionalComponents) : self
	{
		return new self(
			components: self::transformValues([$component, ...$additionalComponents]),
		);
	}

	/**
	 * @param non-empty-array<string|\BackedEnum> $values
	 * @return non-empty-list<string>
	 */
	private static function transformValues (array $values) : array
	{
		return \array_values(
			\array_map(EnumHelper::value(...), $values),
		);
	}

	/**
	 */
	public function createManagementApiData () : array
	{
		$result = [
			"restrict_components" => true,
			"restrict_type" => "",
			"component_group_whitelist" => [],
			"component_group_denylist" => [],
			"component_tag_denylist" => [],
			"component_tag_whitelist" => [],
			"component_whitelist" => [],
			"component_denylist" => [],
		];

		if ([] !== $this->tags)
		{
			return [
				...$result,
				"restrict_type" => "tags",
				"component_tag_whitelist" => $this->tags,
			];
		}


		return [
			...$result,
			"component_whitelist" => $this->components,
		];
	}
}
