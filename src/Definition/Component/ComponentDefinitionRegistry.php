<?php declare(strict_types=1);

namespace Torr\Storyblok\Definition\Component;

final readonly class ComponentDefinitionRegistry
{
	/**
	 * @var array<string, ComponentDefinition[]>
	 */
	private array $componentsByTags;

	/**
	 * @var array<string, ComponentDefinition>
	 */
	private array $definitions;

	/**
	 * @param array<string, ComponentDefinition> $definitions
	 */
	public function __construct (array $definitions)
	{
		// sort components by name
		\uasort(
			$definitions,
			static fn (ComponentDefinition $left, ComponentDefinition $right) => \strnatcasecmp($left->definition->name, $right->definition->name),
		);

		$this->definitions = $definitions;
		$this->componentsByTags = $this->indexTags($definitions);
	}


	/**
	 * @param ComponentDefinition[] $definitions
	 */
	private function indexTags (array $definitions) : array
	{
		$index = [];

		foreach ($definitions as $definition)
		{
			foreach ($definition->definition->tags as $tag)
			{
				$tag = $tag instanceof \BackedEnum
					? $tag->value
					: $tag;
				$index[$tag][] = $definition;
			}
		}

		return $index;
	}


	/**
	 * @return ComponentDefinition[]
	 */
	public function getComponentsByTag (string $tag) : array
	{
		return $this->componentsByTags[$tag] ?? [];
	}

	/**
	 * @return ComponentDefinition[]
	 */
	public function getComponents () : array
	{
		return $this->definitions;
	}
}
