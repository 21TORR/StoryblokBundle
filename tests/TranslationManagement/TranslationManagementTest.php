<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\TranslationManagement;

use PHPUnit\Framework\TestCase;
use Torr\Storyblok\TranslationManagement\TranslationManagement;

/**
 * @internal
 */
final class TranslationManagementTest extends TestCase
{
	/**
	 */
	public function testBasic () : void
	{
		$translationManagement = new TranslationManagement();

		$jsonOriginal = file_get_contents(\sprintf("%s/Data/StoryOriginal.json", __DIR__));
		$story = json_decode($jsonOriginal, true);

		$transformedStoryXml = $translationManagement->transformStory(
			$story,
			[
				"product" => [
					"name",
					"description",
				],
				"quote-block" => [
					"quote",
					"author",
					"anchor-title",
				],
				"text-block" => [
					"text",
					"anchor-title",
				],
			],
		);

		$xmlOriginal = file_get_contents(\sprintf("%s/Data/StoryTranslationXmlOriginal.xml", __DIR__));

		self::assertSame($xmlOriginal, $transformedStoryXml);

		$jsonTranslated = file_get_contents(\sprintf("%s/Data/StoryTranslated.json", __DIR__));
		$storyTranslated = json_decode($jsonTranslated, true);

		$xmlTranslated = file_get_contents(\sprintf("%s/Data/StoryTranslationXmlTranslated.xml", __DIR__));

		$updatedStory = $translationManagement->updateStory($story, $xmlTranslated);

		self::assertSame($storyTranslated, $updatedStory);
	}
}
