<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Field\ApiNormalization;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Torr\Storyblok\Component\AbstractComponent;
use Torr\Storyblok\Component\Config\ComponentType;
use Torr\Storyblok\Field\Choices\StaticChoices;
use Torr\Storyblok\Field\Definition\AssetField;
use Torr\Storyblok\Field\Definition\BooleanField;
use Torr\Storyblok\Field\Definition\ChoiceField;
use Torr\Storyblok\Field\Definition\DateTimeField;
use Torr\Storyblok\Field\Definition\MarkdownField;
use Torr\Storyblok\Field\Definition\NumberField;
use Torr\Storyblok\Field\Definition\TableField;
use Torr\Storyblok\Field\Definition\TextField;
use Torr\Storyblok\Field\Group\EditorTab;
use Torr\Storyblok\Field\Group\FieldGroup;

/**
 * @final
 */
class FieldApiNormalizationTest extends TestCase
{
	/**
	 */
	public static function provideTransform () : iterable
	{
		yield "asset" => [
			["field" => new AssetField("Label")],
			[
				"field" => [
					"type" => "asset",
					"display_name" => "Label",
					"default_value" => null,
					"description" => null,
					"tooltip" => false,
					"translatable" => false,
					"required" => false,
					"regex" => null,
					"allow_external_url" => false,
					"filetypes" => [],
					"pos" => 0,
				],
			],
		];

		yield "boolean" => [
			["field" => new BooleanField("Label")],
			[
				"field" => [
					"type" => "boolean",
					"display_name" => "Label",
					"default_value" => false,
					"description" => null,
					"tooltip" => false,
					"translatable" => false,
					"required" => false,
					"regex" => null,
					"inline_label" => null,
					"pos" => 0,
				],
			],
		];

		yield "datetime" => [
			["field" => new DateTimeField("Label")],
			[
				"field" => [
					"type" => "datetime",
					"display_name" => "Label",
					"default_value" => null,
					"description" => null,
					"tooltip" => false,
					"translatable" => false,
					"required" => false,
					"regex" => null,
					"disable_time" => false,
					"no_translate" => true,
					"pos" => 0,
				],
			],
		];

		yield "markdown" => [
			["field" => new MarkdownField("Label")],
			[
				"field" => [
					"type" => "markdown",
					"display_name" => "Label",
					"default_value" => null,
					"description" => null,
					"tooltip" => false,
					"translatable" => false,
					"required" => false,
					"regex" => null,
					"rich_markdown" => true,
					"rtl" => false,
					"max_length" => null,
					"allow_multiline" => true,
					"no_translate" => false,
					"pos" => 0,
				],
			],
		];

		yield "number" => [
			["field" => new NumberField("Label")],
			[
				"field" => [
					"type" => "number",
					"display_name" => "Label",
					"default_value" => null,
					"description" => null,
					"tooltip" => false,
					"translatable" => false,
					"required" => false,
					"regex" => null,
					"no_translate" => true,
					"pos" => 0,
				],
			],
		];

		yield "option" => [
			["field" => new ChoiceField("Label", new StaticChoices([]))],
			[
				"field" => [
					"type" => "option",
					"display_name" => "Label",
					"default_value" => null,
					"description" => null,
					"tooltip" => false,
					"translatable" => false,
					"required" => false,
					"regex" => null,
					"options" => [],
					"exclude_empty_option" => false,
					"min_options" => null,
					"max_options" => null,
					"no_translate" => true,
					"pos" => 0,
				],
			],
		];

		yield "options" => [
			["field" => new ChoiceField("Label", new StaticChoices([]), allowMultiselect: true)],
			[
				"field" => [
					"type" => "options",
					"display_name" => "Label",
					"default_value" => null,
					"description" => null,
					"tooltip" => false,
					"translatable" => false,
					"required" => false,
					"regex" => null,
					"options" => [],
					"exclude_empty_option" => false,
					"min_options" => null,
					"max_options" => null,
					"no_translate" => true,
					"pos" => 0,
				],
			],
		];

		yield "section" => [
			["field" => new FieldGroup("Label", ["field_inner" => new TextField("Inner")])],
			[
				"field" => [
					"type" => "section",
					"display_name" => "Label",
					"default_value" => null,
					"description" => null,
					"tooltip" => false,
					"translatable" => false,
					"regex" => null,
					"keys" => ["field_inner"],
					"pos" => 0,
				],
				"field_inner" => [
					"type" => "text",
					"display_name" => "Inner",
					"default_value" => null,
					"description" => null,
					"tooltip" => false,
					"translatable" => false,
					"required" => false,
					"regex" => null,
					"rtl" => false,
					"max_length" => null,
					"no_translate" => false,
					"pos" => 1,
				],
			],
		];

		yield "editortab" => [
			["field" => new EditorTab("Label", ["field_inner" => new TextField("Inner")])],
			[
				"field" => [
					"type" => "tab",
					"display_name" => "Label",
					"default_value" => null,
					"description" => null,
					"tooltip" => false,
					"translatable" => false,
					"regex" => null,
					"keys" => ["field_inner"],
					"pos" => 0,
				],
				"field_inner" => [
					"type" => "text",
					"display_name" => "Inner",
					"default_value" => null,
					"description" => null,
					"tooltip" => false,
					"translatable" => false,
					"required" => false,
					"regex" => null,
					"rtl" => false,
					"max_length" => null,
					"no_translate" => false,
					"pos" => 1,
				],
			],
		];

		yield "tab" => [
			["field" => new EditorTab("Label", ["field_inner" => new TextField("Inner")])],
			[
				"field" => [
					"type" => "tab",
					"display_name" => "Label",
					"default_value" => null,
					"description" => null,
					"tooltip" => false,
					"translatable" => false,
					"regex" => null,
					"keys" => ["field_inner"],
					"pos" => 0,
				],
				"field_inner" => [
					"type" => "text",
					"display_name" => "Inner",
					"default_value" => null,
					"description" => null,
					"tooltip" => false,
					"translatable" => false,
					"required" => false,
					"regex" => null,
					"rtl" => false,
					"max_length" => null,
					"no_translate" => false,
					"pos" => 1,
				],
			],
		];

		yield "table" => [
			["field" => new TableField("Label")],
			[
				"field" => [
					"type" => "table",
					"display_name" => "Label",
					"default_value" => null,
					"description" => null,
					"tooltip" => false,
					"translatable" => false,
					"required" => false,
					"regex" => null,
					"pos" => 0,
				],
			],
		];

		yield "text" => [
			["field" => new TextField("Label")],
			[
				"field" => [
					"type" => "text",
					"display_name" => "Label",
					"default_value" => null,
					"description" => null,
					"tooltip" => false,
					"translatable" => false,
					"required" => false,
					"regex" => null,
					"rtl" => false,
					"max_length" => null,
					"no_translate" => false,
					"pos" => 0,
				],
			],
		];

		yield "textarea" => [
			["field" => new TextField("Label", multiline: true)],
			[
				"field" => [
					"type" => "textarea",
					"display_name" => "Label",
					"default_value" => null,
					"description" => null,
					"tooltip" => false,
					"translatable" => false,
					"required" => false,
					"regex" => null,
					"rtl" => false,
					"max_length" => null,
					"no_translate" => false,
					"pos" => 0,
				],
			],
		];

		yield "multiasset" => [
			["field" => new AssetField("Label", allowMultiple: true)],
			[
				"field" => [
					"type" => "multiasset",
					"display_name" => "Label",
					"default_value" => null,
					"description" => null,
					"tooltip" => false,
					"translatable" => false,
					"required" => false,
					"regex" => null,
					"allow_external_url" => false,
					"filetypes" => [],
					"pos" => 0,
				],
			],
		];
	}


	/**
	 */
	#[DataProvider("provideTransform")]
	public function testTransform (array $fields, array $expectedSchema) : void
	{
		$component = $this->createComponent($fields);

		self::assertSame($expectedSchema, $component->toManagementApiData()["schema"]);
	}

	/**
	 */
	private function createComponent (array $fields)
	{
		return new class ($fields) extends AbstractComponent
		{
			public function __construct (
				private readonly array $fields,
			) {}

			public static function getKey () : string
			{
				return "test";
			}

			protected function configureFields () : array
			{
				return $this->fields;
			}

			protected function getComponentType () : ComponentType
			{
				return ComponentType::Standalone;
			}

			public function getDisplayName () : string
			{
				return "Test";
			}
		};
	}
}
