<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Field\Definition;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Torr\Storyblok\Component\Filter\ComponentFilter;
use Torr\Storyblok\Field\Definition\LinkField;
use Torr\Storyblok\Management\ManagementApiData;
use Torr\Storyblok\Manager\Sync\Filter\ResolvableComponentFilter;

/**
 * @internal
 */
final class LinkFieldTest extends TestCase
{
	private static function getApiData (LinkField $field) : array
	{
		$apiData = new ManagementApiData();
		$field->registerManagementApiData("field", $apiData);

		return $apiData->getFullConfig()["field"];
	}

	public function testDefaults () : void
	{
		$actual = self::getApiData(new LinkField("My Label"));

		self::assertSame("multilink", $actual["type"]);
		self::assertSame("My Label", $actual["display_name"]);
		self::assertNull($actual["default_value"]);
		self::assertNull($actual["description"]);
		self::assertFalse($actual["tooltip"]);
		self::assertFalse($actual["translatable"]);
		self::assertFalse($actual["required"]);
		self::assertNull($actual["regex"]);
		// link-specific defaults
		self::assertTrue($actual["email_link_type"]);
		self::assertFalse($actual["asset_link_type"]);
		self::assertTrue($actual["show_anchor"]);
		self::assertFalse($actual["force_link_scope"]);
		self::assertNull($actual["link_scope"]);
		self::assertFalse($actual["allow_target_blank"]);
		self::assertFalse($actual["allow_custom_attributes"]);
		self::assertInstanceOf(ResolvableComponentFilter::class, $actual["component_whitelist"]);
	}

	public static function provideManagementApiData () : iterable
	{
		yield "disable email links" => [
			new LinkField("label", allowEmailLinks: false),
			["email_link_type" => false],
		];

		yield "enable asset links" => [
			new LinkField("label", allowAssetLinks: true),
			["asset_link_type" => true],
		];

		yield "disable anchors" => [
			new LinkField("label", allowAnchors: false),
			["show_anchor" => false],
		];

		yield "internal link scope" => [
			new LinkField("label", internalLinkScope: "articles/"),
			["force_link_scope" => true, "link_scope" => "articles/"],
		];

		yield "no link scope leaves force_link_scope false" => [
			new LinkField("label", internalLinkScope: null),
			["force_link_scope" => false, "link_scope" => null],
		];

		yield "allow target blank" => [
			new LinkField("label", allowTargetBlank: true),
			["allow_target_blank" => true],
		];

		yield "allow custom attributes" => [
			new LinkField("label", allowCustomAttributes: true),
			["allow_custom_attributes" => true],
		];
	}

	/**
	 * @param array<string, mixed> $expected
	 */
	#[DataProvider("provideManagementApiData")]
	public function testManagementApiData (LinkField $field, array $expected) : void
	{
		$actual = self::getApiData($field);

		foreach ($expected as $key => $value)
		{
			self::assertSame($value, $actual[$key] ?? null, "Mismatch for key '{$key}'");
		}
	}

	public static function provideAllowedComponentsProducesResolvableFilter () : iterable
	{
		yield "empty filter (unrestricted)" => [
			new ComponentFilter(),
		];

		yield "filtered by component keys" => [
			ComponentFilter::keys("article", "product"),
		];

		yield "filtered by tags" => [
			ComponentFilter::tags("linkable"),
		];
	}

	#[DataProvider("provideAllowedComponentsProducesResolvableFilter")]
	public function testAllowedComponentsProducesResolvableFilter (ComponentFilter $filter) : void
	{
		$actual = self::getApiData(new LinkField("label", allowedComponents: $filter));

		self::assertInstanceOf(ResolvableComponentFilter::class, $actual["component_whitelist"]);
	}
}
