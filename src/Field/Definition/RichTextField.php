<?php declare(strict_types=1);

namespace Torr\Storyblok\Field\Definition;

use Torr\Storyblok\Component\AbstractComponent;
use Torr\Storyblok\Exception\InvalidFieldConfigurationException;
use Torr\Storyblok\Field\FieldType;
use Torr\Storyblok\Field\RichText\RichTextStyling;
use Torr\Storyblok\Validator\DataValidator;

final class RichTextField extends AbstractField
{
	/**
	 * @inheritDoc
	 *
	 * @param array<class-string<AbstractComponent>> $filterComponents
	 * @param array<string>                          $filterComponentGroups
	 * @param array<RichTextStyling>                 $toolbarOptions
	 * @param array<array<string, string>>           $styleOptions
	 */
	public function __construct (
		string $label,
		mixed $defaultValue = null,
		private readonly ?int $maxLength = null,
		private readonly array $filterComponents = [],
		private readonly array $filterComponentGroups = [],
		private readonly array $toolbarOptions = [],
		private readonly array $styleOptions = [],
	)
	{
		parent::__construct($label, $defaultValue);

		if (!empty($this->filterComponents) && !empty($this->filterComponentGroups))
		{
			throw new InvalidFieldConfigurationException("You can't filter both component groups and components");
		}
	}

	/**
	 * @inheritDoc
	 */
	protected function getInternalStoryblokType () : FieldType
	{
		return FieldType::RichText;
	}

	/**
	 * @inheritDoc
	 */
	public function toManagementApiData (int $position) : array
	{
		$formattedStyleOptions = [];

		foreach ($this->styleOptions as $name => $value)
		{
			$formattedStyleOptions[] = [
				"name" => $name,
				"value" => $value,
			];
		}

		return \array_replace(
			parent::toManagementApiData($position),
			[
				"max_length" => $this->maxLength,
				"customize_toolbar" => !empty($this->toolbarOptions),
				"toolbar" => \array_map(
					static fn (RichTextStyling $option) => $option->value,
					$this->toolbarOptions,
				),
				"restrict_type" => !empty($this->filterComponentGroups) ? "groups" : "",
				"restrict_components" => !empty($this->filterComponents) || !empty($this->filterComponentGroups),
				"component_whitelist" => $this->filterComponents,
				"component_group_whitelist" => $this->filterComponentGroups,
				"style_options" => $formattedStyleOptions,
			],
		);
	}

	/**
	 * @inheritDoc
	 */
	public function validateData (DataValidator $validator, array $path, mixed $data) : void
	{
		// @todo add implementation
	}
}