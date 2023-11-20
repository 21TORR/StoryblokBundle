<?php declare(strict_types=1);

namespace Torr\Storyblok\Field\Definition;

use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Type;
use Torr\Storyblok\Context\ComponentContext;
use Torr\Storyblok\Field\FieldType;
use Torr\Storyblok\Visitor\DataVisitorInterface;

final class DateTimeField extends AbstractField
{
	private const DATE_TIME_FORMAT = "Y-m-d H:i";

	/**
	 * @inheritDoc
	 */
	public function __construct (
		string $label,
		private readonly bool $withTimeSelection = true,
		mixed $defaultValue = null,
	)
	{
		parent::__construct($label, $defaultValue);
	}

	/**
	 * @inheritDoc
	 */
	protected function toManagementApiData () : array
	{
		return \array_replace(
			parent::toManagementApiData(),
			[
				"disable_time" => !$this->withTimeSelection,
				// never allow to export this field to translate (as it would need to be a perfect format match)
				"no_translate" => true,
			],
		);
	}

	/**
	 * @inheritDoc
	 */
	protected function getInternalStoryblokType () : FieldType
	{
		return FieldType::DateTime;
	}

	/**
	 * @inheritDoc
	 */
	public function validateData (ComponentContext $context, array $contentPath, mixed $data, array $fullData) : void
	{
		$context->ensureDataIsValid(
			$contentPath,
			$this,
			$data,
			[
				!$this->allowMissingData && $this->required ? new NotNull() : null,
				new Type("string"),
				new DateTime(self::DATE_TIME_FORMAT),
			],
		);
	}

	/**
	 * @inheritDoc
	 */
	public function transformData (
		mixed $data,
		ComponentContext $context,
		array $fullData,
		?DataVisitorInterface $dataVisitor = null,
	) : ?\DateTimeImmutable
	{
		\assert(null === $data || \is_string($data));

		// empty fields are also passed as ""
		$data = $context->normalizeOptionalString($data);

		$transformed = null !== $data
			? \DateTimeImmutable::createFromFormat(self::DATE_TIME_FORMAT, $data)
			: null;

		if (false === $transformed)
		{
			$context->logger->error("Encountered invalid date time: {value}", [
				"value" => $data,
			]);
			$transformed = null;
		}

		// force 00:00:00 if not with time selection
		if (null !== $transformed && !$this->withTimeSelection)
		{
			$transformed = $transformed->setTime(0, 0);
		}

		$dataVisitor?->onDataVisit($this, $transformed);
		return $transformed;
	}
}
