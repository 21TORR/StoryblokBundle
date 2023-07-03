<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Field\Helper;

use Torr\Storyblok\Exception\InvalidComponentConfigurationException;
use Torr\Storyblok\Field\Definition\TextField;
use Torr\Storyblok\Field\Helper\FieldDefinitionHelper;
use PHPUnit\Framework\TestCase;

class FieldDefinitionHelperTest extends TestCase
{
	/**
	 *
	 */
	public static function testNone () : void
	{
		FieldDefinitionHelper::ensureMaximumOneAdminDisplayName([
			"one" => (new TextField("Text 1")),
			"two" => (new TextField("Text 2")),
		]);

		self::assertTrue(true, "should be fine");
	}

	/**
	 *
	 */
	public function testOne () : void
	{
		FieldDefinitionHelper::ensureMaximumOneAdminDisplayName([
			"one" => (new TextField("Text 1"))
				->useAsAdminDisplayName(),
			"two" => (new TextField("Text 2")),
		]);

		self::assertTrue(true, "should be fine");
	}

	/**
	 *
	 */
	public function testMultiple () : void
	{
		$this->expectException(InvalidComponentConfigurationException::class);
		$this->expectExceptionMessage("Can't use multiple fields as admin display name");

		FieldDefinitionHelper::ensureMaximumOneAdminDisplayName([
			"one" => (new TextField("Text 1"))
				->useAsAdminDisplayName(),
			"two" => (new TextField("Text 2"))
				->useAsAdminDisplayName(),
		]);
	}
}
