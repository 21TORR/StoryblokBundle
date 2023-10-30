<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Field\Definition;

use PHPUnit\TextUI\XmlConfiguration\Validator;
use Psr\Log\NullLogger;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\ValidatorBuilder;
use Torr\Storyblok\Context\ComponentContext;
use Torr\Storyblok\Exception\Story\InvalidDataException;
use Torr\Storyblok\Field\Choices\StaticChoices;
use Torr\Storyblok\Field\Definition\ChoiceField;
use PHPUnit\Framework\TestCase;
use Torr\Storyblok\Image\ImageDimensionsExtractor;
use Torr\Storyblok\Manager\ComponentManager;
use Torr\Storyblok\Transformer\DataTransformer;
use Torr\Storyblok\Validator\DataValidator;

class ChoiceFieldTest extends TestCase
{
	public static function provideValid () : iterable
	{
		$defaultChoices = new StaticChoices([
			"label1" => "key1",
			"label2" => "key2",
			"label3" => "key3",
			"raw-value1" => "1",
			"raw-value2" => "2",
		]);

		yield "single select: optional, null" => [
			new ChoiceField("label", $defaultChoices, false),
			null,
		];

		// unfortunately, Storyblok provides empty strings for empty choices
		yield "single select: optional, empty string" => [
			new ChoiceField("label", $defaultChoices, false),
			"",
		];

		yield "single select: optional, valid" => [
			new ChoiceField("label", $defaultChoices, false),
			"key1",
		];

		yield "single select: normalization" => [
			new ChoiceField("label", $defaultChoices,false),
			1,
		];

		yield "multi select: optional, null" => [
			new ChoiceField("label", $defaultChoices, true),
			null,
		];

		yield "multi select: optional, empty array" => [
			new ChoiceField("label", $defaultChoices, true),
			[],
		];

		yield "multi select: optional, valid" => [
			new ChoiceField("label", $defaultChoices, true),
			["key1", "key2"],
		];

		yield "multi select: required, valid" => [
			(new ChoiceField("label", $defaultChoices, true))
				->enableValidation(),
			["key1", "key2"],
		];

		yield "multi select: normalization" => [
			new ChoiceField("label", $defaultChoices,true),
			[1],
		];

		yield "multi select: min count" => [
			new ChoiceField(
				"label",
				$defaultChoices,
				true,
				minimumNumberOfOptions: 1
			),
			["key1"],
		];

		yield "multi select: max count" => [
			new ChoiceField(
				"label",
				$defaultChoices,
				true,
				maximumNumberOfOptions: 2,
			),
			["key1"],
		];

		yield "multi select: min + max count" => [
			new ChoiceField(
				"label",
				$defaultChoices,
				true,
				minimumNumberOfOptions: 1,
				maximumNumberOfOptions: 2,
			),
			["key1"],
		];
	}


	/**
	 * @dataProvider provideValid
	 */
	public function testValid (ChoiceField $field, mixed $data) : void
	{
		$context = $this->createComponentContext();
		$field->validateData($context, [], $data, []);
		self::assertTrue(true, "should not throw");
	}


	public static function provideInvalid () : iterable
	{
		$defaultChoices = new StaticChoices([
			"key1" => "value1",
			"key2" => "value2",
			"key3" => "value3",
		]);

		yield "single select: required, null" => [
			(new ChoiceField("label", $defaultChoices, false))
				->enableValidation(),
			null,
		];

		// unfortunately, Storyblok provides empty strings for empty choices
		yield "single select: required, empty string" => [
			(new ChoiceField("label", $defaultChoices, false))
				->enableValidation(),
			"",
		];

		yield "single select: optional, invalid value" => [
			new ChoiceField("label", $defaultChoices, false),
			"invalid-key",
		];

		yield "multi select: required, null" => [
			(new ChoiceField("label", $defaultChoices, true))
				->enableValidation(),
			null,
		];

		yield "multi select: required, empty array" => [
			(new ChoiceField("label", $defaultChoices, true))
				->enableValidation(),
			[],
		];

		yield "multi select: invalid value" => [
			new ChoiceField("label", $defaultChoices, true),
			["invalid-key"],
		];

		yield "multi select: min count" => [
			new ChoiceField(
				"label",
				$defaultChoices,
				true,
				minimumNumberOfOptions: 2,
			),
			["key1"],
		];

		yield "multi select: max count" => [
			new ChoiceField(
				"label",
				$defaultChoices,
				true,
				maximumNumberOfOptions: 1,
			),
			["key1", "key2"],
		];

		// validate that all invalid values are properly found
		foreach (["single" => false, "multiple" => true] as $label => $allowMultiselect)
		{
			foreach ([
				"bool" => true,
				"float" => 4.5,
				"object" => new \stdClass(),
			] as $valueLabel => $value)
			{
				yield "{$label} select: invalid type {$valueLabel}" => [
					new ChoiceField(
						"label",
						$defaultChoices,
						$allowMultiselect,
					),
					$value,
				];

				yield "{$label} select: invalid type {$valueLabel} array" => [
					new ChoiceField(
						"label",
						$defaultChoices,
						$allowMultiselect,
					),
					[$value],
				];
			}
		}
	}

	/**
	 * @dataProvider provideInvalid
	 */
	public function testInvalid (ChoiceField $field, mixed $data) : void
	{
		$this->expectException(InvalidDataException::class);

		$context = $this->createComponentContext();
		$field->validateData($context, [], $data, []);
	}


	/**
	 */
	private function createComponentContext () : ComponentContext
	{
		return new ComponentContext(
			$this->createMock(ComponentManager::class),
			new DataTransformer(),
			new NullLogger(),
			new DataValidator(Validation::createValidator()),
			new ImageDimensionsExtractor(),
		);
	}
}
