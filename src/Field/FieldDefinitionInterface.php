<?php declare(strict_types=1);

namespace Torr\Storyblok\Field;

use Torr\Storyblok\Context\StoryblokContext;
use Torr\Storyblok\Validator\DataValidator;
use Torr\Storyblok\Visitor\DataVisitorInterface;

interface FieldDefinitionInterface
{
	/**
	 * Transforms the type to the type definition required for the Storyblok API
	 *
	 * @internal
	 */
	public function toManagementApiData (
		int $position,
	) : array;

	/**
	 * Validates the data for this field, as it was sent by Storyblok.
	 */
	public function validateData (
		DataValidator $validator,
		array $path,
		mixed $data,
	) : void;

	/**
	 * Receives the Storyblok data for the given field and transforms it for better usage
	 */
	public function transformValue (
		mixed $data,
		StoryblokContext $dataContext,
		?DataVisitorInterface $dataVisitor = null,
	) : mixed;
}
