<?php declare(strict_types=1);

namespace Torr\Storyblok\Field\Definition;

use Torr\Storyblok\Component\Filter\ComponentFilter;
use Torr\Storyblok\Exception\InvalidFieldConfigurationException;
use Torr\Storyblok\Field\FieldType;
use Torr\Storyblok\Manager\Sync\Filter\ResolvableComponentFilter;

/**
 * The `no_translate` option doesn't make sense here, as the field itself has no content and the content is
 * managed by their translatable settings.
 */
final class BloksField extends AbstractField
{
	public function __construct (
		string $label,
		private readonly ?int $minimumNumberOfBloks = null,
		private readonly ?int $maximumNumberOfBloks = null,
		public readonly ComponentFilter $allowedComponents = new ComponentFilter(),
	)
	{
		parent::__construct($label);

		if (
			null !== $this->minimumNumberOfBloks
			&& null !== $this->maximumNumberOfBloks
			&& $this->minimumNumberOfBloks > $this->maximumNumberOfBloks
		)
		{
			throw new InvalidFieldConfigurationException(
				"The minimum number of blocks value can't be higher than the maximum",
			);
		}
	}

	/**
	 * @inheritDoc
	 */
	public function toManagementApiData () : array
	{
		return array_replace(
			parent::toManagementApiData(),
			[
				"minimum" => $this->minimumNumberOfBloks,
				"maximum" => $this->maximumNumberOfBloks,
				"component_whitelist" => new ResolvableComponentFilter($this->allowedComponents, "component_whitelist", "restrict_components"),
			],
		);
	}

	/**
	 * @inheritDoc
	 */
	protected function getInternalStoryblokType () : FieldType
	{
		return FieldType::Bloks;
	}
}
