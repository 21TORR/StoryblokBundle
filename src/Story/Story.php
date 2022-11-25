<?php declare(strict_types=1);

namespace Torr\Storyblok\Story;

use Torr\Storyblok\Component\AbstractComponent;
use Torr\Storyblok\Context\ComponentContext;
use Torr\Storyblok\Visitor\DataVisitorInterface;

abstract class Story
{
	protected readonly StoryMetaData $metaData;
	protected readonly array $content;

	/**
	 */
	final public function __construct (
		array $data,
		protected readonly AbstractComponent $rootComponent,
		protected readonly ComponentContext $dataContext,
	)
	{
		$this->content = $data["content"];
		$this->metaData = new StoryMetaData($data, $this->rootComponent::getKey());
	}


	/**
	 */
	public function getMetaData () : StoryMetaData
	{
		return $this->metaData;
	}

	public function validate (ComponentContext $context) : void
	{
		$this->rootComponent->validateData($context, $this->content);
	}


	/**
	 * Returns the transformed data for a single field
	 */
	protected function getTransformedFieldData (
		string $fieldName,
		?DataVisitorInterface $dataVisitor = null,
	) : mixed
	{
		return $this->rootComponent->transformField(
			$this->content,
			$fieldName,
			$this->dataContext,
			$dataVisitor,
		);
	}

	/**
	 *
	 */
	public function __debugInfo () : ?array
	{
		return [
			// hide implementations of these, as they have huge dependency trees
			"\0*\0rootComponent" => \get_class($this->rootComponent),
			"\0*\0dataContext" => "...",
		];
	}
}
