<?php declare(strict_types=1);

namespace Torr\Storyblok\Definition\Mapping;

use Torr\Storyblok\Definition\Exception\InvalidComponentDefinitionException;
use Torr\Storyblok\Definition\Filter\ComponentFilter;
use Torr\Storyblok\Definition\Field\MappedField;
use Torr\Storyblok\Field\FieldType;

/**
 * @final
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
readonly class BloksField extends MappedField
{
	/**
	 * @param non-negative-int $minimumNumberOfBloks
	 * @param positive-int|null $maximumNumberOfBloks
	 */
	public function __construct (
		string $label,
		public ComponentFilter $allow,
		public int $minimumNumberOfBloks = 0,
		public ?int $maximumNumberOfBloks = null,
		?string $key = null,
	)
	{
		parent::__construct($key, $label);

		if (null !== $this->maximumNumberOfBloks && $this->maximumNumberOfBloks < $this->minimumNumberOfBloks)
		{
			throw new InvalidComponentDefinitionException(\sprintf(
				"The maximum number of bloks (%d) can't be lower than the minimum (%d).",
				$this->maximumNumberOfBloks,
				$this->minimumNumberOfBloks,
			));
		}

	}

	/**
	 *
	 */
	#[\Override]
	public function getType () : FieldType
	{
		return FieldType::Bloks;
	}

	/**
	 *
	 */
	#[\Override]
	public function createManagementApiData () : ?array
	{
		$restriction = [
			"restrict_components" => true,
		];


		// pass like this, so that null values are explicitly included
		return \array_replace(
			$this->mergeManagementData(
				$this->allow->createManagementApiData(),
			),
			[
				"minimum" => $this->minimumNumberOfBloks,
				"maximum" => $this->maximumNumberOfBloks,
			],
		);
	}
}
