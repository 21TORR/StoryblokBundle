<?php declare(strict_types=1);

namespace Torr\Storyblok\Manager\Sync\Diff;

use SebastianBergmann\Diff\Differ;
use SebastianBergmann\Diff\Output\UnifiedDiffOutputBuilder;
use Torr\Storyblok\Exception\InvalidComponentConfigurationException;

final class ComponentConfigDiffer
{
	private const IGNORED_KEYS = [
		"all_presets",
		"created_at",
		"id",
		"preset_id",
		"real_name",
		"updated_at",
	];

	private readonly Differ $differ;

	/**
	 */
	public function __construct ()
	{
		$builder = new UnifiedDiffOutputBuilder(
			"--- Currenty in Storyblok\n+++ Modified local Config\n",
		);
		$this->differ = new Differ($builder);
	}


	/**
	 * @return string[]|null
	 */
	public function diff (
		array $storyblokConfig,
		array $localConfig,
	) : ?array
	{
		$diff = \trim($this->differ->diff(
			$this->formatAsJson($storyblokConfig),
			$this->formatAsJson($localConfig),
		));

		$lines = \explode("\n", $diff);

		return \count($lines) > 2
			? $this->parseDiffOutputLines($lines)
			: null;
	}


	/**
	 *
	 */
	private function parseDiffOutputLines (array $lines) : array
	{
		$result = [];

		foreach ($lines as $line)
		{
			$result[] = match ($line[0] ?? "")
			{
				"+" => "<fg=green>{$line}</>",
				"-" => "<fg=red>{$line}</>",
				"@" => $line,
				default => "<fg=gray>{$line}</>",
			};
		}

		return $result;
	}


	/**
	 * Formats the given array as normalized JSON
	 */
	private function formatAsJson (array $array) : string
	{
		try
		{

			return \json_encode(
				$this->normalizeArray($array),
				\JSON_PRETTY_PRINT | \JSON_THROW_ON_ERROR | \JSON_UNESCAPED_UNICODE,
			);
		}
		catch (\JsonException $exception)
		{
			throw new InvalidComponentConfigurationException(\sprintf(
				"Failed to diff component config: %s",
				$exception->getMessage(),
			), previous: $exception);
		}
	}

	/**
	 * Normalizes the config arrays, by sorting them and removing ignored keys
	 */
	private function normalizeArray (array $array, int $level = 0) : array
	{
		// sort array
		\uksort($array, "strnatcasecmp");
		$result = [];

		foreach ($array as $key => $value)
		{
			if (0 === $level && \in_array($key, self::IGNORED_KEYS, true))
			{
				continue;
			}

			$result[$key] = \is_array($value)
				? $this->normalizeArray($value, $level + 1)
				: $value;
		}

		return $result;
	}
}
