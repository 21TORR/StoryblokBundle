<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Definition\Data;

use Torr\Storyblok\Definition\Data\FieldDefinition;
use PHPUnit\Framework\TestCase;
use Torr\Storyblok\Definition\Exception\InvalidFieldDefinitionException;
use Torr\Storyblok\Definition\Mapping\TextField;

class FieldDefinitionTest extends TestCase
{
	public function testReservedKey ()
	{
		$this->expectException(InvalidFieldDefinitionException::class);
		$this->expectExceptionMessage("Can't use field name 'component' as it is a reserved name.");

		new FieldDefinition(
			"component",
			"test",
			new TextField("test"),
			null,
		);
	}
}
