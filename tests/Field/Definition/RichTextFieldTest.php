<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Field\Definition;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Torr\Storyblok\Component\Filter\ComponentFilter;
use Torr\Storyblok\Field\Definition\RichTextField;
use Torr\Storyblok\Field\RichText\RichTextStyling;
use Torr\Storyblok\Management\ManagementApiData;
use Torr\Storyblok\Manager\Sync\Filter\ResolvableComponentFilter;

/**
 * @internal
 */
final class RichTextFieldTest extends TestCase
{
	private static function getApiData (RichTextField $field) : array
	{
		$apiData = new ManagementApiData();
		$field->registerManagementApiData("field", $apiData);

		return $apiData->getFullConfig()["field"];
	}

	public function testDefaults () : void
	{
		$actual = self::getApiData(new RichTextField("My Label"));

		self::assertSame("richtext", $actual["type"]);
		self::assertSame("My Label", $actual["display_name"]);
		self::assertNull($actual["default_value"]);
		self::assertNull($actual["description"]);
		self::assertFalse($actual["tooltip"]);
		self::assertFalse($actual["translatable"]);
		self::assertFalse($actual["required"]);
		self::assertNull($actual["regex"]);
		self::assertNull($actual["max_length"]);
		self::assertFalse($actual["customize_toolbar"]);
		self::assertSame([], $actual["toolbar"]);
		self::assertSame([], $actual["style_options"]);
		self::assertInstanceOf(ResolvableComponentFilter::class, $actual["component_whitelist"]);
	}

	public function testMaxLength () : void
	{
		$actual = self::getApiData(new RichTextField("label", maxLength: 1000));

		self::assertSame(1000, $actual["max_length"]);
	}

	public function testToolbarOptions () : void
	{
		$actual = self::getApiData(new RichTextField(
			"label",
			toolbarOptions: [RichTextStyling::Bold, RichTextStyling::Italic, RichTextStyling::Link],
		));

		self::assertTrue($actual["customize_toolbar"]);
		self::assertSame(["bold", "italic", "link"], $actual["toolbar"]);
	}

	public function testEmptyToolbarDisablesCustomization () : void
	{
		$actual = self::getApiData(new RichTextField("label", toolbarOptions: []));

		self::assertFalse($actual["customize_toolbar"]);
		self::assertSame([], $actual["toolbar"]);
	}

	public function testStyleOptions () : void
	{
		$actual = self::getApiData(new RichTextField(
			"label",
			styleOptions: [
				"Highlighted" => "text-highlight",
				"Muted" => "text-muted",
			],
		));

		self::assertSame(
			[
				["name" => "Highlighted", "value" => "text-highlight"],
				["name" => "Muted", "value" => "text-muted"],
			],
			$actual["style_options"],
		);
	}

	public static function provideAllowedComponents () : iterable
	{
		yield "empty filter (unrestricted)" => [
			new ComponentFilter(),
		];

		yield "filtered by component keys" => [
			ComponentFilter::keys("teaser", "hero"),
		];

		yield "filtered by tags" => [
			ComponentFilter::tags("embeddable"),
		];
	}

	#[DataProvider("provideAllowedComponents")]
	public function testAllowedComponentsProducesResolvableFilter (ComponentFilter $filter) : void
	{
		$actual = self::getApiData(new RichTextField("label", allowedComponents: $filter));

		self::assertInstanceOf(ResolvableComponentFilter::class, $actual["component_whitelist"]);
	}
}
