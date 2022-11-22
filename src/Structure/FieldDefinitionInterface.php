<?php declare(strict_types=1);

namespace Torr\Storyblok\Structure;

use Torr\Storyblok\Transformer\DataTransformer;
use Torr\Storyblok\Validator\DataValidator;

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
		DataTransformer $dataTransformer,
		?DataVisitorInterface $dataVisitor = null,
	) : mixed;
}
