<?php declare(strict_types=1);

namespace Torr\Storyblok\Field\Choices;

use Torr\Storyblok\Context\ComponentContext;
use Torr\Storyblok\Exception\InvalidFieldConfigurationException;

/**
 * @template T of BackedEnumChoiceInterface
 */
class EnumChoices extends StaticChoices
{
	/**
	 * @param class-string<T> $enumType
	 */
	public function __construct (
		private readonly string $enumType,
		bool $showEmptyOption = true,
	)
	{
		if (!\is_a($this->enumType, BackedEnumChoiceInterface::class, true))
		{
			throw new InvalidFieldConfigurationException(\sprintf(
				"Enum type in EnumChoices must implement %s, but %s given",
				BackedEnumChoiceInterface::class,
				$this->enumType,
			));
		}

		parent::__construct(
			$this->generateChoicesList(),
			$showEmptyOption,
		);
	}

	/**
	 */
	private function generateChoicesList () : array
	{
		$result = [];

		foreach ($this->enumType::cases() as $value)
		{
			$result[$value->getLabel()] = $value->value;
		}

		return $result;
	}

	/**
	 * @inheritDoc
	 *
	 * @return T
	 */
	public function transformData (ComponentContext $context, int|string $data) : BackedEnumChoiceInterface
	{
		return $this->enumType::from($data);
	}


	/**
	 * @inheritDoc
	 */
	public function isValidData (
		int|string $data,
		?ComponentContext $context = null,
	) : bool
	{
		return null !== $this->enumType::tryFrom($data);
	}
}
