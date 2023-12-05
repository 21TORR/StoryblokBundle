<?php declare(strict_types=1);

namespace Torr\Storyblok\Component\Definition;

use Torr\Storyblok\Component\Config\ComponentIcon;
use Torr\Storyblok\Exception\InvalidComponentConfigurationException;

final class ComponentDefinition
{
	/**
	 */
	public function __construct (
		/**
		 * The name of the field that is rendered below the component's name in the backend, in order to more
		 * easily identify a certain component.
		 *
		 * E.g. If this component has a Headline, you might want to use this one as a Preview Field Name.
		 *
		 * @deprecated use the parameter on the fields instead
		 */
		public readonly ?string $previewFieldName = null,
		/**
		 * A URL to an image, which has been uploaded to the Storyblok Asset Library that shows
		 * a live representation of what the component will look like.
		 */
		public readonly ?string $previewScreenshotUrl = null,
		/**
		 * Define a custom Squirrelly template that renders the component's preview instead of the Preview Field Name.
		 *
		 * See {@see https://www.storyblok.com/docs/schema-configuration#preview-template}.
		 */
		public readonly ?string $previewTemplate = null,
		/**
		 * The name of the icon that is used to identify this component.
		 *
		 * For a list of available icon names, see {@see STORYBLOK.md} or the `blockIcons` variable in {@see https://github.com/storyblok/storyblok-design-system/blob/master/src/lib/internal-icons.js}.
		 */
		public readonly ?ComponentIcon $icon = null,
		/**
		 * A hex-code, including the `#` that provides a background color for the component's icon.
		 */
		public readonly ?string $iconBackgroundColor = null,
	)
	{
		if (null !== $this->iconBackgroundColor && !\preg_match('~^#(\\d{3}|\\d{6})$~', $this->iconBackgroundColor))
		{
			throw new InvalidComponentConfigurationException(\sprintf(
				"Invalid component configuration: icon background color must be hex with #, but is %s",
				$this->iconBackgroundColor,
			));
		}
	}
}
