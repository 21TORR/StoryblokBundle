<?php declare(strict_types=1);

namespace Torr\Storyblok\Field\Definition;

use Symfony\Component\Validator\Constraints\DateTime;
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
	public function toManagementApiData (int $position) : array
	{
		return \array_replace(
			parent::toManagementApiData($position),
			[
				"disable_time" => !$this->withTimeSelection,
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
	public function validateData (ComponentContext $context, array $contentPath, mixed $data) : void
	{
		$context->ensureDataIsValid(
			$contentPath,
			$this,
			$data,
			[
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
		?DataVisitorInterface $dataVisitor = null,
	) : ?\DateTimeImmutable
	{
		\assert(null === $data || \is_string($data));

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

		return parent::transformData($transformed, $context, $dataVisitor);
	}
}
