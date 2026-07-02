<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Field\ApiNormalization;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Torr\Storyblok\Component\AbstractComponent;
use Torr\Storyblok\Component\Config\ComponentType;
use Torr\Storyblok\Field\Definition\TextField;

/**
 * @final
 */
class FieldApiNormalizationTest extends TestCase
{
	/**
	 */
	public static function provideTransform () : iterable
	{
		yield "empty" => [
			[
				"text" => new TextField("Text Label"),
			],
			[
				"text" => [
					'type' => 'text',
					"display_name" => 'Text Label',
					'default_value' => null,
					'description' => null,
					'tooltip' => false,
					'translatable' => false,
					'required' => false,
					'regex' => null,
					'rtl' => false,
					'max_length' => null,
					'no_translate' => false,
					'pos' => 0,
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
