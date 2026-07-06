<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Story\Hydrator;

use Psr\Log\NullLogger;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Tests\Torr\Storyblok\Fixtures\Components\Simple;
use Torr\Storyblok\Context\ComponentContext;
use Torr\Storyblok\Definition\DefinitionRegistry;
use Torr\Storyblok\Definition\Loader\ComponentDefinitionLoader;
use Torr\Storyblok\Definition\Loader\FieldDefinitionLoader;
use Torr\Storyblok\Image\ImageDimensionsExtractor;
use Torr\Storyblok\Manager\ComponentManager;
use Torr\Storyblok\Story\Hydrator\MetaDataHydrator;
use Torr\Storyblok\Story\Hydrator\StoryHydrator;
use PHPUnit\Framework\TestCase;
use Torr\Storyblok\Transformer\DataTransformer;
use Torr\Storyblok\Validator\DataValidator;

class StoryHydratorTest extends TestCase
{
	private function createComponentContext () : ComponentContext
	{
		return new ComponentContext(
			componentManager: self::createMock(ComponentManager::class),
			dataTransformer: new DataTransformer(),
			logger: new NullLogger(),
			validator: new DataValidator(),
			imageDimensionsExtractor: new ImageDimensionsExtractor(),
		);
	}

	/**
	 *
	 */
	public function testHydration ()
	{
		$definitionLoader = new ComponentDefinitionLoader(new FieldDefinitionLoader());
		$definitionRegistry = new DefinitionRegistry();
		$definitionRegistry->register($definitionLoader->loadDefinition(Simple::class));
		$hydrator = new StoryHydrator(
			definitionRegistry: $definitionRegistry,
			accessor: PropertyAccess::createPropertyAccessor(),
			componentContext: $this->createComponentContext(),
			metaDataHydrator: new MetaDataHydrator(),
		);

		$story = $hydrator->hydrateDocument([
			"name" => "Link Test",
			"created_at" => "2026-03-04T20:56:15.030Z",
			"published_at" => "2026-07-02T13:14:29.734Z",
			"updated_at" => "2026-07-02T13:14:29.745Z",
			"id" => 151421644948932,
			"uuid" => "8a2d7e40-0ce0-46e7-9f6c-f90ae5e11993",
			"content" => [
				"_uid" => "4f1ab87c-f8dc-4a08-930e-3ba9c0960e50",
				"component" => "simple",
				"text" => "abc",
			],
			"slug" => "link-test",
			"full_slug" => "link-test",
			"sort_by_date" => null,
			"position" => -40,
			"tag_list" => [],
			"is_startpage" => false,
			"parent_id" => null,
			"meta_data" => null,
			"group_id" => "0848e8fe-edc2-4a35-862f-4722708187e0",
			"first_published_at" => "2026-03-04T20:56:42.178Z",
			"release_id" => null,
			"lang" => "default",
			"path" => null,
			"alternates" => [],
			"default_full_slug" => null,
    		"translated_slugs" => null
		], "123", 1);

		dump($story);
	}
}
