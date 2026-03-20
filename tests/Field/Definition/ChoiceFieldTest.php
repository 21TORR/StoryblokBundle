<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Field\Definition;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Torr\Storyblok\Context\ComponentContext;
use Torr\Storyblok\Exception\Story\InvalidDataException;
use Torr\Storyblok\Field\Choices\DatasourceChoices;
use Torr\Storyblok\Field\Choices\LanguagesChoices;
use Torr\Storyblok\Field\Choices\RemoteJsonChoices;
use Torr\Storyblok\Field\Choices\StaticChoices;
use Torr\Storyblok\Field\Choices\StoryChoices;
use Torr\Storyblok\Field\Definition\ChoiceField;
use Torr\Storyblok\Image\ImageDimensionsExtractor;
use Torr\Storyblok\Management\ManagementApiData;
use Torr\Storyblok\Manager\ComponentManager;
use Torr\Storyblok\Manager\Sync\Filter\ResolvableComponentFilter;
use Torr\Storyblok\Transformer\DataTransformer;
use Torr\Storyblok\Validator\DataValidator;

/**
 * @internal
 */
final class ChoiceFieldTest extends TestCase
{
	/**
	 *
	 */
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
			new ChoiceField("label", $defaultChoices, false),
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
			new ChoiceField("label", $defaultChoices, true),
			[1],
		];

		yield "multi select: min count" => [
			new ChoiceField(
				"label",
				$defaultChoices,
				true,
				minimumNumberOfOptions: 1,
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
	 *
	 */
	#[DataProvider("provideValid")]
	public function testValid (ChoiceField $field, mixed $data) : void
	{
		$context = $this->createComponentContext();
		$field->validateData($context, [], $data, []);
		self::assertTrue(true, "should not throw");
	}

	/**
	 *
	 */
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
	 *
	 */
	#[DataProvider("provideInvalid")]
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
			self::createStub(ComponentManager::class),
			new DataTransformer(),
			new NullLogger(),
			new DataValidator(),
			new ImageDimensionsExtractor(),
		);
	}

	// region Management API Data

	private static function getApiData (ChoiceField $field) : array
	{
		$apiData = new ManagementApiData();
		$field->registerManagementApiData("field", $apiData);

		return $apiData->getFullConfig()["field"];
	}

	public function testManagementApiDefaultsSingleSelect () : void
	{
		$actual = self::getApiData(new ChoiceField("My Label", new StaticChoices([])));

		self::assertSame("option", $actual["type"]);
		self::assertSame("My Label", $actual["display_name"]);
		self::assertNull($actual["default_value"]);
		self::assertNull($actual["description"]);
		self::assertFalse($actual["tooltip"]);
		self::assertFalse($actual["translatable"]);
		self::assertFalse($actual["required"]);
		self::assertNull($actual["regex"]);
		// choice fields are never exported for translation
		self::assertTrue($actual["no_translate"]);
		self::assertNull($actual["min_options"]);
		self::assertNull($actual["max_options"]);
	}

	public function testManagementApiDefaultsMultiSelect () : void
	{
		$actual = self::getApiData(new ChoiceField("label", new StaticChoices([]), allowMultiselect: true));

		self::assertSame("options", $actual["type"]);
	}

	public function testMinMaxOptionsAreStrings () : void
	{
		$actual = self::getApiData(new ChoiceField(
			"label",
			new StaticChoices([]),
			allowMultiselect: true,
			minimumNumberOfOptions: 1,
			maximumNumberOfOptions: 3,
		));

		// Storyblok requires these as strings, not integers
		self::assertSame("1", $actual["min_options"]);
		self::assertSame("3", $actual["max_options"]);
	}

	public function testStaticChoices () : void
	{
		$actual = self::getApiData(new ChoiceField("label", new StaticChoices([
			"Option A" => "a",
			"Option B" => "b",
		])));

		self::assertSame(
			[
				["name" => "Option A", "value" => "a"],
				["name" => "Option B", "value" => "b"],
			],
			$actual["options"],
		);
		self::assertFalse($actual["exclude_empty_option"]);
	}

	public function testStaticChoicesHideEmptyOption () : void
	{
		$actual = self::getApiData(new ChoiceField("label", new StaticChoices([], showEmptyOption: false)));

		self::assertTrue($actual["exclude_empty_option"]);
	}

	public function testDatasourceChoices () : void
	{
		$actual = self::getApiData(new ChoiceField("label", new DatasourceChoices("my-datasource")));

		self::assertSame("internal", $actual["source"]);
		self::assertSame("my-datasource", $actual["datasource_slug"]);
		self::assertFalse($actual["exclude_empty_option"]);
	}

	public function testDatasourceChoicesHideEmptyOption () : void
	{
		$actual = self::getApiData(new ChoiceField("label", new DatasourceChoices("ds", showEmptyOption: false)));

		self::assertTrue($actual["exclude_empty_option"]);
	}

	public function testRemoteJsonChoices () : void
	{
		$actual = self::getApiData(new ChoiceField("label", new RemoteJsonChoices("https://example.com/options.json")));

		self::assertSame("external", $actual["source"]);
		self::assertSame("https://example.com/options.json", $actual["external_datasource"]);
		self::assertFalse($actual["exclude_empty_option"]);
	}

	public function testRemoteJsonChoicesHideEmptyOption () : void
	{
		$actual = self::getApiData(new ChoiceField("label", new RemoteJsonChoices("https://example.com/options.json", showEmptyOption: false)));

		self::assertTrue($actual["exclude_empty_option"]);
	}

	public function testLanguagesChoices () : void
	{
		$actual = self::getApiData(new ChoiceField("label", new LanguagesChoices()));

		self::assertSame("internal_languages", $actual["source"]);
		self::assertFalse($actual["exclude_empty_option"]);
	}

	public function testLanguagesChoicesHideEmptyOption () : void
	{
		$actual = self::getApiData(new ChoiceField("label", new LanguagesChoices(showEmptyOption: false)));

		self::assertTrue($actual["exclude_empty_option"]);
	}

	public function testStoryChoicesDefaults () : void
	{
		$actual = self::getApiData(new ChoiceField("label", new StoryChoices()));

		self::assertSame("internal_stories", $actual["source"]);
		self::assertSame("", $actual["folder_slug"]);
		self::assertSame("link", $actual["entry_appearance"]);
		self::assertFalse($actual["allow_advanced_search"]);
		self::assertInstanceOf(ResolvableComponentFilter::class, $actual["filter_content_type"]);
	}

	public function testStoryChoicesCard () : void
	{
		$actual = self::getApiData(new ChoiceField("label", new StoryChoices(displayAsCard: true)));

		self::assertSame("card", $actual["entry_appearance"]);
	}

	public function testStoryChoicesAdvancedSearch () : void
	{
		$actual = self::getApiData(new ChoiceField("label", new StoryChoices(allowAdvancedSearch: true)));

		self::assertTrue($actual["allow_advanced_search"]);
	}

	public function testStoryChoicesRestrictToPath () : void
	{
		$actual = self::getApiData(new ChoiceField("label", new StoryChoices(restrictToPath: "blog/")));

		self::assertSame("blog/", $actual["folder_slug"]);
	}

	// endregion
}
