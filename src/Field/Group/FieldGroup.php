<?php declare(strict_types=1);

namespace Torr\Storyblok\Field\Group;

use Torr\Storyblok\Context\StoryblokContext;
use Torr\Storyblok\Field\Definition\AbstractField;
use Torr\Storyblok\Field\FieldDefinitionInterface;
use Torr\Storyblok\Field\FieldType;
use Torr\Storyblok\Field\NestedFieldDefinitionInterface;
use Torr\Storyblok\Validator\DataValidator;
use Torr\Storyblok\Visitor\DataVisitorInterface;

final class FieldGroup extends AbstractField implements NestedFieldDefinitionInterface
{
	public function __construct (
		string $label,
		/** @var array<string, FieldDefinitionInterface> $fields */
		private readonly array $fields,
	)
	{
		parent::__construct($label);
	}

	/**
	 * @inheritDoc
	 */
	protected function getInternalStoryblokType () : FieldType
	{
		return FieldType::Section;
	}

	/**
	 * @inheritDoc
	 */
	public function toManagementApiData (int $position, ) : array
	{
		return \array_replace(
			parent::toManagementApiData($position),
			[
				"keys" => \array_keys($this->fields),
			],
		);
	}

	/**
	 * @inheritDoc
	 */
	public function getNestedFields () : array
	{
		return $this->fields;
	}

	/**
	 * @inheritDoc
	 */
	public function validateData (DataValidator $validator, array $contentPath, mixed $data, ) : void
	{
	}

	/**
	 * @inheritDoc
	 */
	public function transformData (
		mixed $data,
		StoryblokContext $dataContext,
		?DataVisitorInterface $dataVisitor = null,
	) : mixed
	{
		return $data;
	}
}
