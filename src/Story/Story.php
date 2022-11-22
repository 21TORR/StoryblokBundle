<?php declare(strict_types=1);

namespace Torr\Storyblok\Story;

use Torr\Storyblok\Field\FieldDefinitionInterface;
use Torr\Storyblok\Transformer\DataTransformer;
use Torr\Storyblok\Validator\DataValidator;

abstract class Story
{
	protected readonly StoryAttributes $attributes;
	protected readonly array $content;

	/**
	 */
	final public function __construct (
		array $data,
		/** @var array<string, FieldDefinitionInterface> $fieldDefinitions */
		protected readonly array $fieldDefinitions,
		protected readonly DataTransformer $dataTransformer,
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

	}
}
