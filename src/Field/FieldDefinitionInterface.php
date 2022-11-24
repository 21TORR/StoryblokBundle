<?php declare(strict_types=1);

namespace Torr\Storyblok\Field;

use Torr\Storyblok\Context\ComponentContext;
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
		ComponentContext $context,
		array $contentPath,
		mixed $data,
	) : void;

	/**
	 * Receives the Storyblok data for the given field and transforms it for better usage
	 */
	public function transformData (
		mixed $data,
		ComponentContext $context,
		?DataVisitorInterface $dataVisitor = null,
	) : mixed;
}
