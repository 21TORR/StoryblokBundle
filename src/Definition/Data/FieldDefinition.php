<?php declare(strict_types=1);

namespace Torr\Storyblok\Definition\Data;

use Torr\Storyblok\Definition\Field\MappedField;
use Torr\Storyblok\Definition\Mapping\Required;
use Torr\Storyblok\Definition\Mapping\Translatable;

/**
 * Definition of a single field
 */
final readonly class FieldDefinition
{
	/**
	 */
	public function __construct (
		public string $key,
		public string $propertyPath,
		public MappedField $mapping,
		public mixed $propertyType,
		public ?Required $required = null,
		public ?Translatable $translatable = null,
	) {}


	/**
	 * Returns the field data VO for the API
	 */
	public function createApiData (string $key) : ?array
	{
		$apiData = $this->mapping->createManagementApiData($key);

		if (null === $apiData)
		{
			return null;
		}

		$apiData["required"] = null !== $this->required;
		$apiData["no_translate"] = null === $this->translatable;

		return $apiData;
	}
}
