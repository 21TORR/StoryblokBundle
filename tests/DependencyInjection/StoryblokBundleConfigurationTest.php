<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;
use Torr\Storyblok\DependencyInjection\StoryblokBundleConfiguration;

/**
 * @internal
 */
final class StoryblokBundleConfigurationTest extends TestCase
{
	/**
	 */
	public function testDefaultValues () : void
	{
		$result = $this->process([]);

		self::assertSame(
			[
				"automatically_sync_definitions" => [
					"staging" => false,
					"production" => false,
				],
			],
			$result,
		);
	}

	/**
	 */
	public function testBooleanShorthandTrue () : void
	{
		$result = $this->process([
			[
				"automatically_sync_definitions" => true,
			],
		]);

		self::assertSame(
			[
				"automatically_sync_definitions" => [
					"staging" => true,
					"production" => true,
				],
			],
			$result,
		);
	}

	/**
	 */
	public function testBooleanShorthandFalse () : void
	{
		$result = $this->process([
			[
				"automatically_sync_definitions" => false,
			],
		]);

		self::assertSame(
			[
				"automatically_sync_definitions" => [
					"staging" => false,
					"production" => false,
				],
			],
			$result,
		);
	}

	/**
	 */
	public function testExplicitPerEnvironmentValues () : void
	{
		$result = $this->process([
			[
				"automatically_sync_definitions" => [
					"staging" => true,
					"production" => false,
				],
			],
		]);

		self::assertSame(
			[
				"automatically_sync_definitions" => [
					"staging" => true,
					"production" => false,
				],
			],
			$result,
		);
	}

	/**
	 */
	public function testExplicitPartialValuesUseDefaultsForMissingKeys () : void
	{
		$result = $this->process([
			[
				"automatically_sync_definitions" => [
					"staging" => true,
				],
			],
		]);

		self::assertSame(
			[
				"automatically_sync_definitions" => [
					"staging" => true,
					"production" => false,
				],
			],
			$result,
		);
	}

	/**
	 * Later configs must override earlier ones, like when merging multiple config files.
	 */
	public function testMultipleConfigsAreMerged () : void
	{
		$result = $this->process([
			[
				"automatically_sync_definitions" => true,
			],
			[
				"automatically_sync_definitions" => [
					"production" => false,
				],
			],
		]);

		self::assertSame(
			[
				"automatically_sync_definitions" => [
					"staging" => true,
					"production" => false,
				],
			],
			$result,
		);
	}

	/**
	 * @param array<int, array<string, mixed>> $configs
	 *
	 * @return array<string, mixed>
	 */
	private function process (array $configs) : array
	{
		$processor = new Processor();

		return $processor->processConfiguration(new StoryblokBundleConfiguration(), $configs);
	}
}
