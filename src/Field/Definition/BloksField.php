<?php declare(strict_types=1);

namespace Torr\Storyblok\Field\Definition;

use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Type;
use Torr\Storyblok\Component\Filter\ComponentFilter;
use Torr\Storyblok\Context\ComponentContext;
use Torr\Storyblok\Exception\Component\UnknownComponentKeyException;
use Torr\Storyblok\Exception\InvalidFieldConfigurationException;
use Torr\Storyblok\Exception\Story\InvalidDataException;
use Torr\Storyblok\Field\FieldType;
use Torr\Storyblok\Manager\Sync\Filter\ResolvableComponentFilter;
use Torr\Storyblok\Visitor\DataVisitorInterface;

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
		private readonly ComponentFilter $allowedComponents = new ComponentFilter(),
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
	}

	/**
	 * @inheritDoc
	 */
	public function toManagementApiData () : array
	{
		return \array_replace(
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

	/**
	 * @inheritDoc
	 */
	public function validateData (ComponentContext $context, array $contentPath, mixed $data, array $fullData) : void
	{
		// first validate structure itself
		$context->ensureDataIsValid(
			$contentPath,
			$this,
			$data,
			[
				!$this->allowMissingData && $this->required ? new NotNull() : null,
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

		// Abort if the data is null. It either was catched above
		// or otherwise it is an allowed state.
		if (null === $data)
		{
			return;
		}

		\assert(\is_array($data));
		$noOfKnownContainedBloks = 0;

		// then validate nested structure
		foreach ($data as $componentData)
		{
			try
			{

				$component = $context->getComponentByKey($componentData["component"]);
				$component->validateData(
					$context,
					$componentData,
					$contentPath,
				);
				++$noOfKnownContainedBloks;
			}
			catch (UnknownComponentKeyException)
			{
				// ignore the actual error, as we just want to ignore unknown components
			}
		}

		if (null !== $this->minimumNumberOfBloks && $noOfKnownContainedBloks < $this->minimumNumberOfBloks)
		{
			throw new InvalidDataException(
				\sprintf("Found %d (known) components, but was expecting at least %d", $noOfKnownContainedBloks, $this->minimumNumberOfBloks),
				$contentPath,
				$this,
				$data,
			);
		}

		if (null !== $this->maximumNumberOfBloks && $noOfKnownContainedBloks > $this->maximumNumberOfBloks)
		{
			throw new InvalidDataException(
				\sprintf("Found %d (known) components, but was expecting at most %d", $noOfKnownContainedBloks, $this->maximumNumberOfBloks),
				$contentPath,
				$this,
				$data,
			);
		}
	}

	/**
	 * @inheritDoc
	 */
	public function transformData (
		mixed $data,
		ComponentContext $context,
		array $fullData,
		?DataVisitorInterface $dataVisitor = null,
	) : array
	{
		\assert(null === $data || \is_array($data));
		$transformed = [];

		if (\is_array($data))
		{
			foreach ($data as $componentData)
			{
				\assert(\is_array($componentData));

				try
				{
					$component = $context->getComponentByKey($componentData["component"]);
					$transformed[] = $component->transformData($componentData, $context, $dataVisitor);
				}
				catch (UnknownComponentKeyException $exception)
				{
					// ignore unknown components
					$context->logger->warning("Unknown component key {key} found in BloksField, skipping.", [
						"key" => $exception->componentKey,
						"exception" => $exception,
					]);
				}
			}
		}

		$dataVisitor?->onDataVisit($this, $transformed);
		return $transformed;
	}

	/**
	 * @inheritDoc
	 */
	#[\Override]
	public function useAsAdminDisplayName (?bool $canSync = null, ) : static
	{
		throw new InvalidFieldConfigurationException("This field type cannot be used as admin display name. Only text-based fields can be used.");
	}
}
