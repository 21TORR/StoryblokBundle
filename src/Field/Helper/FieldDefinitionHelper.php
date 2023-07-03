<?php declare(strict_types=1);

namespace Torr\Storyblok\Field\Helper;

use Torr\Storyblok\Exception\InvalidComponentConfigurationException;
use Torr\Storyblok\Field\Definition\AbstractField;
use Torr\Storyblok\Field\FieldDefinitionInterface;

final class FieldDefinitionHelper
{
	/**
	 * Ensures that there is a only a single field with an admin display preview
	 *
	 * @param iterable<FieldDefinitionInterface> $fields
	 */
	public static function ensureMaximumOneAdminDisplayName (iterable $fields) : void
	{
		$fieldWithPreview = null;

		foreach ($fields as $field)
		{
			if ($field instanceof AbstractField && $field->isStoryblokPreviewField())
			{
				if (null !== $fieldWithPreview)
				{
					throw new InvalidComponentConfigurationException("Can't use multiple fields as admin display name");
				}

				$fieldWithPreview = $field;
			}
		}
	}
}
