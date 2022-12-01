<?php declare(strict_types=1);

namespace Torr\Storyblok\Field\Definition;

use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Type;
use Torr\Storyblok\Component\Reference\ComponentsWithTags;
use Torr\Storyblok\Context\ComponentContext;
use Torr\Storyblok\Exception\Component\UnknownComponentKeyException;
use Torr\Storyblok\Exception\InvalidFieldConfigurationException;
use Torr\Storyblok\Exception\Story\InvalidDataException;
use Torr\Storyblok\Field\FieldType;
use Torr\Storyblok\Visitor\DataVisitorInterface;

final class BloksField extends AbstractField
{
	public function __construct (
		string $label,
		private readonly ?int $minimumNumberOfBloks = null,
		private readonly ?int $maximumNumberOfBloks = null,
		/** @var array<string>|ComponentsWithTags|null $filterComponents */
		private readonly array|ComponentsWithTags|null $filterComponents = null,
		private readonly array $filterComponentGroups = [],
	)
	{
		parent::__construct($label);

		if (
			null !== $this->minimumNumberOfBloks
			&& null !== $this->maximumNumberOfBloks
			&& $this->minimumNumberOfBloks > $this->maximumNumberOfBloks
		)
		{
			throw new InvalidFieldConfigurationException(\sprintf(
				"The minimum number of blocks value can't be higher than the maximum",
			));
		}

		if (!empty($this->filterComponents) && !empty($this->filterComponentGroups))
		{
			throw new InvalidFieldConfigurationException("You can't filter both component groups and components");
		}
	}

	/**
	 * @inheritDoc
	 */
	public function toManagementApiData (int $position, ) : array
	{
		return \array_replace(
			parent::toManagementApiData($position),
			[
				"minimum" => $this->minimumNumberOfBloks,
				"maximum" => $this->maximumNumberOfBloks,
				"restrict_type" => !empty($this->filterComponentGroups) ? "groups" : "",
				"restrict_components" => !empty($this->filterComponents) || !empty($this->filterComponentGroups),
				"component_whitelist" => $this->filterComponents,
				"component_group_whitelist" => $this->filterComponentGroups,
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

	/**
	 * @inheritDoc
	 */
	public function validateData (ComponentContext $context, array $contentPath, mixed $data) : void
	{
		// first validate structure itself
		$context->ensureDataIsValid(
			$contentPath,
			$this,
			$data,
			[
				new NotNull(),
				new Type("array"),
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

		// then validate nested structure
		try
		{
			foreach ($data as $componentData)
			{
				$component = $context->getComponentByKey($componentData["component"]);
				$component->validateData(
					$context,
					$data,
					$contentPath,
				);
			}
		}
		catch (UnknownComponentKeyException $exception)
		{
			throw new InvalidDataException(
				\sprintf(
					"Validation the bloks data failed: %s",
					$exception->getMessage(),
				),
				$contentPath,
				$this,
				$data,
				previous: $exception,
			);
		}
	}

	/**
	 * @inheritDoc
	 */
	public function transformData (
		mixed $data,
		ComponentContext $context,
		?DataVisitorInterface $dataVisitor = null,
	) : array
	{
		\assert(null === $data || \is_array($data));
		$transformed = [];

		if (\is_array($data))
		{
			foreach ($data as $componentData)
			{
				$component = $context->getComponentByKey($componentData["component"]);
				$transformed[] = $component->transformData($componentData, $context, $dataVisitor);
			}
		}

		return parent::transformData($transformed, $context, $dataVisitor);
	}
}
