<?php declare(strict_types=1);

namespace Torr\Storyblok\Story;

use Torr\Storyblok\Component\AbstractComponent;
use Torr\Storyblok\Context\StoryblokContext;
use Torr\Storyblok\Validator\DataValidator;
use Torr\Storyblok\Visitor\DataVisitorInterface;

abstract class Story
{
	protected readonly StoryAttributes $attributes;
	protected readonly array $content;

	/**
	 */
	final public function __construct (
		array $data,
		protected readonly AbstractComponent $rootComponent,
		protected readonly StoryblokContext $dataContext,
	)
	{
		$this->content = $data["content"];
		$this->attributes = new StoryAttributes($data);
	}


	/**
	 */
	public function getAttributes () : StoryAttributes
	{
		return $this->attributes;
	}

	public function validate (DataValidator $validator) : void
	{
		$this->rootComponent->validateData($validator, $this->content);
	}

	/**
	 * Returns the base transformed data
	 */
	public function getTransformedData (
		?DataVisitorInterface $dataVisitor = null,
	) : array
	{
		return $this->rootComponent->transformData(
			$this->content,
			$this->dataContext,
			$dataVisitor,
		);
	}
}
