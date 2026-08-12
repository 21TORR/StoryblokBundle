<?php declare(strict_types=1);

namespace Torr\Storyblok\Definition\Filter;

use Torr\Storyblok\Definition\Exception\Filter\InvalidComponentFilterException;
use Torr\Storyblok\Helper\EnumHelper;

final readonly class ComponentFilter
{
	/** @var list<string> */
	public array $tags;
	/** @var list<string> */
	public array $keys;

	/**
	 * @param list<string|\BackedEnum> $tags
	 * @param list<string|\BackedEnum> $keys
	 */
	public function __construct (
		/** @var list<string|\BackedEnum> */
		array $tags = [],
		/** @var list<string|\BackedEnum> */
		array $keys = [],
	)
	{
		if ([] !== $tags && [] !== $keys)
		{
			throw new InvalidComponentFilterException("Component filter must only have tags or keys set, not both");
		}

		if ([] === $tags && [] === $keys)
		{
			throw new InvalidComponentFilterException("Component filter must at least have tags or keys set");
		}

		$this->tags = $this->transformValues($tags);
		$this->keys = $this->transformValues($keys);
	}

	/**
	 * @param non-empty-array<string|\BackedEnum> $values
	 * @return non-empty-list<string>
	 */
	private function transformValues (array $values) : array
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
			"component_whitelist" => $this->keys,
		];
	}
}
