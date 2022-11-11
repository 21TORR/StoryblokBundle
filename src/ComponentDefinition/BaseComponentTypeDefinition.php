<?php declare(strict_types=1);

namespace Torr\Storyblok\ComponentDefinition;

use Torr\Storyblok\ComponentFieldDefinition\FieldDefinition;
use Torr\Storyblok\Data\DataSerializationContext;
use Torr\Storyblok\Data\StoryblokComponentIconName;
use Torr\Storyblok\Data\StoryblokComponentType;
use Torr\Storyblok\Exception\IllegalFieldNameException;

abstract class BaseComponentTypeDefinition
{
	abstract public static function getComponentType () : StoryblokComponentType;

	/**
	 * A sluggified name that is used to reference to this component.
	 */
	abstract public static function getTechnicalName () : string;

	/**
	 * A human-friendly name.
	 */
	abstract public static function getDisplayName () : string;

	/**
	 * @return FieldDefinition[]
	 */
	abstract public static function getFields (DataSerializationContext $context) : array;

	/**
	 * The name of the field that is rendered below the component's name in the backend, in order to more
	 * easily identify a certain component.
	 *
	 * E.g. If this component has a Headline, you might want to use this one as a Preview Field Name.
	 */
	public static function getPreviewFieldName () : ?string
	{
		return null;
	}

	/**
	 * Define a custom Squirrelly template that renders the component's preview instead of the Preview Field Name.
	 *
	 * See {@see https://www.storyblok.com/docs/schema-configuration#preview-template}.
	 */
	public static function getPreviewTemplate () : ?string
	{
		return null;
	}

	/**
	 * A URL to an image, which has been uploaded to the Storyblok Asset Library that shows
	 * a live representation of what the component will look like.
	 */
	public static function getPreviewScreenshotUrl () : ?string
	{
		return null;
	}

	public static function getRealName () : ?string
	{
		return null;
	}

	/**
	 * The name of the Group, that this Component should be associated to.
	 */
	public static function getComponentGroupName () : ?ComponentGroups
	{
		return null;
	}

	/**
	 * The name of the icon that is used to identify this component.
	 *
	 * For a list of available icon names, see {@see STORYBLOK.md} or the `blockIcons` variable in {@see https://github.com/storyblok/storyblok-design-system/blob/master/src/lib/internal-icons.js}.
	 */
	public static function getComponentIconName () : ?StoryblokComponentIconName
	{
		return null;
	}

	/**
	 * A hex-code, including the `#` that provides a background color for the component's icon.
	 */
	public static function getComponentColorCode () : ?string
	{
		return null;
	}

	/**
	 * Returns the API representation of a Component type definition
	 */
	final public static function toApiData (
		DataSerializationContext $context,
	) : array
	{
		$fields = [];

		foreach (static::getFields($context) as $fieldName => $fieldDefinition)
		{
			if ("component" === $fieldName)
			{
				throw new IllegalFieldNameException(
					"The name 'component' is an illegal/a reserved field name and can't be used without breaking Storyblok.",
				);
			}

			$fields[$fieldName] = \array_filter(
				$fieldDefinition->toApiData($context),
				static fn (mixed $data) => null !== $data && "" !== $data && [] !== $data,
			);
		}

		return [
			"name" => static::getTechnicalName(),
			"display_name" => static::getDisplayName(),
			"schema" => $fields,
			"image" => static::getPreviewScreenshotUrl(),
			"preview" => static::getPreviewFieldName(),
			"preview_tmpl" => static::getPreviewTemplate(),
			"real_name" => static::getRealName(),
			"color" => static::getComponentColorCode(),
			"icon" => static::getComponentIconName()?->value,
			...static::getComponentType()->toApiData(),
		];
	}
}
