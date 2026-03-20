<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Manager\Sync\Diff;

use PHPUnit\Framework\TestCase;
use Torr\Storyblok\Manager\Sync\Diff\ComponentConfigDiffer;

/**
 * @internal
 */
final class ComponentConfigDifferTest extends TestCase
{
	/**
	 */
	public function testDiffReturnsNullIfOnlyIgnoredRootKeysDiffer () : void
	{
		$differ = new ComponentConfigDiffer();

		$storyblokConfig = [
			"name" => "teaser",
			"schema" => [
				"title" => [
					"type" => "text",
				],
			],
			"id" => 123,
			"updated_at" => "2026-03-20",
		];
		$localConfig = [
			"name" => "teaser",
			"schema" => [
				"title" => [
					"type" => "text",
				],
			],
		];

		self::assertNull($differ->diff($storyblokConfig, $localConfig));
	}

	/**
	 */
	public function testDiffHighlightsAddedAndRemovedLines () : void
	{
		$differ = new ComponentConfigDiffer();

		$storyblokConfig = [
			"name" => "teaser",
			"schema" => [
				"title" => [
					"type" => "text",
				],
			],
		];
		$localConfig = [
			"name" => "teaser",
			"schema" => [
				"title" => [
					"type" => "markdown",
				],
			],
		];

		$diff = $differ->diff($storyblokConfig, $localConfig);

		self::assertIsArray($diff);
		self::assertContains("+++ Adding in Storyblok", array_map(static fn (string $line) => strip_tags($line), $diff));
		self::assertContains("<fg=green>+            \"type\": \"markdown\"</>", $diff);
		self::assertContains("<fg=red>-            \"type\": \"text\"</>", $diff);
	}

	/**
	 */
	public function testDiffIgnoresNestedIdKeysOnLevelTwo () : void
	{
		$differ = new ComponentConfigDiffer();

		$storyblokConfig = [
			"name" => "teaser",
			"schema" => [
				"title" => [
					"type" => "text",
					"id" => 1,
				],
			],
		];
		$localConfig = [
			"name" => "teaser",
			"schema" => [
				"title" => [
					"type" => "text",
					"id" => 999,
				],
			],
		];

		self::assertNull($differ->diff($storyblokConfig, $localConfig));
	}
}
