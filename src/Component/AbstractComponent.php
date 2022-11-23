<?php declare(strict_types=1);

namespace Torr\Storyblok\Component;

use Torr\Storyblok\Component\Config\ComponentType;
use Torr\Storyblok\Component\Definition\ComponentDefinition;
use Torr\Storyblok\Context\StoryblokContext;
use Torr\Storyblok\Exception\InvalidComponentConfigurationException;
use Torr\Storyblok\Field\FieldDefinitionInterface;
use Torr\Storyblok\Field\NestedFieldDefinitionInterface;
use Torr\Storyblok\Story\Story;
use Torr\Storyblok\Visitor\DataVisitorInterface;

/**
 * Base class for all components registered in the system
 *
 * @template TStory of Story
 */
abstract class AbstractComponent
{
	/** @var array<string, FieldDefinitionInterface>|null */
	private ?array $fields = null;

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
	 * @return class-string<TStory>
	 */
	abstract public function getStoryClass () : string;

	/**
	 * Receives the Storyblok data for the given field and transforms it for better usage
	 */
	public function transformValue (
		mixed $data,
		StoryblokContext $dataContext,
		?DataVisitorInterface $dataVisitor = null,
	) : array
	{
		$transformedData = [];

		foreach ($this->getFieldCollection() as $name => $field)
		{
			$fieldData = $field instanceof NestedFieldDefinitionInterface
				? $data
				: ($data[$name] ?? null);

			$field->transformValue(
				$fieldData,
				$dataContext,
				$dataVisitor,
			);
		}

		return $transformedData;
	}

	/**
	 * @return array<string, FieldDefinitionInterface>
	 */
	final protected function getFieldCollection () : array
	{
		if (null === $this->fields)
		{
			$this->fields = $this->configureFields();
		}

		return $this->fields;
	}


	/**
	 * Normalizes the fields for usage in the management API
	 *
	 * @param array<FieldDefinitionInterface> $fields
	 * @param array<string, mixed>            $normalizedFields
	 */
	private function normalizeFields (
		array $fields,
		array $normalizedFields = [],
	) : array
	{
		if (empty($fields))
		{
			throw new InvalidComponentConfigurationException(\sprintf(
				"Invalid component '%s': can't have a component without fields",
				static::class,
			));
		}

		foreach ($fields as $key => $field)
		{
			if (ComponentHelper::isReservedKey($key))
			{
				throw new InvalidComponentConfigurationException(\sprintf(
					"Invalid component configuration '%s': can't use '%s' as field key, as that is a reserved key.",
					static::class,
					$key,
				));
			}

			if (\array_key_exists($key, $normalizedFields))
			{
				throw new InvalidComponentConfigurationException(\sprintf(
					"Invalid component configuration '%s': field key '%s' used more than once",
					static::class,
					$key,
				));
			}

			$normalizedFields[$key] = $field->toManagementApiData(\count($normalizedFields));

			if ($field instanceof NestedFieldDefinitionInterface)
			{
				$normalizedFields = $this->normalizeFields($field->getNestedFields(), $normalizedFields);
			}
		}

		return $normalizedFields;
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
			"schema" => $this->normalizeFields($this->getFieldCollection()),
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
