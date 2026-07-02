<?php declare(strict_types=1);

namespace Torr\Storyblok\Manager\Sync\Diff;

use Jfcherng\Diff\DiffHelper;
use Jfcherng\Diff\Renderer\RendererConstant;
use Torr\Storyblok\Exception\InvalidComponentConfigurationException;

final class ComponentConfigDiffer
{
	private const array IGNORED_LEVEL_0_KEYS = [
		"all_presets",
		"content_type_asset_preview",
		"created_at",
		"id",
		"internal_tag_ids",
		"internal_tags_list",
		"metadata",
		"preset_id",
		"real_name",
		"updated_at",
	];
	private const array IGNORED_LEVEL_2_KEYS = [
		"id",
	];

	/**
	 * @return string[]|null
	 */
	public function diff (
		array $storyblokConfig,
		array $localConfig,
	) : ?array
	{
		$diff = trim(DiffHelper::calculate(
			$this->formatAsJson($storyblokConfig),
			$this->formatAsJson($localConfig),
			rendererOptions: [
				"cliColorization" => RendererConstant::CLI_COLOR_DISABLE
			],
		));

		if ("" === $diff)
		{
			return null;
		}

		$lines = explode("\n", $diff);

		// add header here, so that we can cleanly check for an empty diff above
		return $this->parseDiffOutputLines([
			"+++ Adding in Storyblok",
			"--- Removing in Storyblok",
			...$lines,
		]);
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
			return json_encode(
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
		uksort($array, "strnatcasecmp");
		$result = [];

		foreach ($array as $key => $value)
		{
			if (0 === $level && \in_array($key, self::IGNORED_LEVEL_0_KEYS, true))
			{
				continue;
			}

			if (2 === $level && \in_array($key, self::IGNORED_LEVEL_2_KEYS, true))
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
