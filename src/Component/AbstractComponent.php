<?php declare(strict_types=1);

namespace Torr\Storyblok\Component;

use Torr\Storyblok\Component\Config\ComponentType;
use Torr\Storyblok\Component\Definition\ComponentDefinition;
use Torr\Storyblok\Exception\InvalidComponentConfigurationException;
use Torr\Storyblok\Field\FieldDefinitionInterface;

/**
 * Base class for all components registered in the system
 */
abstract class AbstractComponent
{
	/**
	 * Returns the unique key for this component
	 */
	abstract public static function getKey () : string;

	/**
	 * Configures the component
	 */
	protected function configureComponent () : ComponentDefinition
	{
		return new ComponentDefinition();
	}

	/**
	 * Configures the fields in this component
	 *
	 * @return array<string, FieldDefinitionInterface>
	 */
	abstract protected function configureFields () : array;

	/**
	 * Returns the type of this component
	 */
	abstract protected function getComponentType () : ComponentType;

	/**
	 * Returns the human-readable name of this component
	 */
	abstract public function getDisplayName () : string;

	/**
	 * Returns the component group display name
	 */
	public function getComponentGroup () : ?string
	{
		return null;
	}

	/**
	 * Returns the tags of this component
	 *
	 * @return string[]
	 */
	public function getTags () : array
	{
		return [];
	}

	/**
	 * Normalizes the fields for usage in the management API
	 */
	private function normalizeFields () : array
	{
		$normalized = [];
		$fields = $this->configureFields();

		if (empty($fields))
		{
			throw new InvalidComponentConfigurationException(\sprintf(
				"Invalid component '%s': can't have a component without fields",
				static::class,
			));
		}

		foreach ($this->configureFields() as $key => $field)
		{
			if (ComponentHelper::isReservedKey($key))
			{
				throw new InvalidComponentConfigurationException(\sprintf(
					"Invalid component configuration '%s': can't use '%s' as field key, as that is a reserved key.",
					static::class,
					$key,
				));
			}

			$normalized[$key] = $field->toManagementApiData(\count($normalized));
		}

		return $normalized;
	}

	/**
	 * Transforms the data for the component
	 *
	 * @internal
	 */
	final public function toManagementApiData () : array
	{
		if (ComponentHelper::isReservedKey(static::getKey()))
		{
			throw new InvalidComponentConfigurationException(\sprintf(
				"Invalid component configuration '%s': can't use '%s' as component key, as that is a reserved key.",
				static::class,
				static::getKey(),
			));
		}

		$definition = $this->configureComponent();

		return [
			"name" => static::getKey(),
			"display_name" => $this->getDisplayName(),
			"schema" => $this->normalizeFields(),
			"image" => $definition->previewScreenshotUrl,
			"preview" => $definition->previewFieldName,
			"preview_tmpl" => $definition->previewTemplate,
			"real_name" => static::getKey(),
			"color" => $definition->iconBackgroundColor,
			"icon" => $definition->icon?->value,
			...$this->getComponentType()->toManagementApiData(),
		];
	}
}
