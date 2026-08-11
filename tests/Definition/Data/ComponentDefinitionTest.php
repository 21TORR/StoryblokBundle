<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Definition\Data;

use PHPUnit\Framework\TestCase;
use Torr\Storyblok\Component\Config\ComponentType;
use Torr\Storyblok\Definition\Data\ComponentDefinition;
use Torr\Storyblok\Definition\Exception\InvalidComponentDefinitionException;

/**
 * @internal
 */
final class ComponentDefinitionTest extends TestCase
{
	/**
	 *
	 */
	public function testEmptyFields () : void
	{
		$this->expectException(InvalidComponentDefinitionException::class);
		$this->expectExceptionMessage("Can't create component without fields in story 'My\\Story'.");

		new ComponentDefinition(
			"My\\Story",
			"test",
			"Label",
			ComponentType::Standalone,
			fields: [],
		);
	}
}
