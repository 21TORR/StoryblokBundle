<?php declare(strict_types=1);

namespace Torr\Storyblok\Mapping\Field;

use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Type;
use Torr\Storyblok\Component\Filter\ComponentFilter;
use Torr\Storyblok\Exception\InvalidFieldConfigurationException;
use Torr\Storyblok\Field\FieldType;
use Torr\Storyblok\Hydrator\StoryHydrator;
use Torr\Storyblok\Data\Validator\DataValidator;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class BloksField extends AbstractField
{
	public function __construct (
		string $key,
		?string $label = null,
		private readonly ?int $minimumNumberOfBloks = null,
		private readonly ?int $maximumNumberOfBloks = null,
		private readonly ComponentFilter $allowedComponents = new ComponentFilter(),
	)
	{
		parent::__construct(
			internalStoryblokType: FieldType::Bloks,
			key: $key,
			label: $label,
		);

		if (null !== $this->minimumNumberOfBloks && $this->minimumNumberOfBloks < 0)
		{
			throw new InvalidFieldConfigurationException(\sprintf(
				"The minimum number of blocks (%d) can't be negative",
				$this->minimumNumberOfBloks,
			));
		}

		if (null !== $this->maximumNumberOfBloks && $this->maximumNumberOfBloks < 0)
		{
			throw new InvalidFieldConfigurationException(\sprintf(
				"The maximum number of blocks (%d) can't be negative",
				$this->maximumNumberOfBloks,
			));
		}

		if (
			null !== $this->minimumNumberOfBloks
			&& null !== $this->maximumNumberOfBloks
			&& $this->minimumNumberOfBloks > $this->maximumNumberOfBloks
		)
		{
			throw new InvalidFieldConfigurationException(\sprintf(
				"The minimum number of blocks (%d) value can't be higher than the maximum (%d)",
				$this->minimumNumberOfBloks,
				$this->maximumNumberOfBloks,
			));
		}
	}

	/**
	 * @inheritDoc
	 */
	public function validateData (array $contentPath, DataValidator $validator, mixed $data) : void
	{
		$data ??= [];

		$validator->ensureDataIsValid(
			$contentPath,
			$data,
			[
				new Type("array"),
				null !== $this->minimumNumberOfBloks || null !== $this->maximumNumberOfBloks
					? new Count(
						min: $this->minimumNumberOfBloks,
						max: $this->maximumNumberOfBloks,
					)
					: null,
				new All(
					constraints: [
						new NotNull(),
						new Type("array"),
						new Collection(
							fields: [
								"component" => [
									new NotNull(),
									new Type("string"),
								],
							],
							allowExtraFields: true,
							allowMissingFields: false,
						),
					],
				),
			],
		);
	}

	/**
	 * @inheritDoc
	 */
	public function transformRawData (array $contentPath, mixed $data, StoryHydrator $hydrator) : array
	{
		$data ??= [];
		\assert(\is_array($data));

		$result = [];

		foreach ($data as $index => $entry)
		{
			$type = $entry["component"];
			$result[] = $hydrator->hydrateBlok(
				[...$contentPath, \sprintf("Index #%d", $index)],
				$type,
				$entry,
			);
		}

		return $result;
	}
}
