<?php declare(strict_types=1);

namespace Torr\Storyblok\Tiptap\Helper;

class FixBrokenLinksMarksHelper
{
	public function fixJson (string $rawJson) : string
	{
		try
		{
			$json = json_decode($rawJson, true, 512, \JSON_THROW_ON_ERROR);

			if (!\is_array($json))
			{
				return $rawJson;
			}

			$modifiedJson = [];
			$nodeContent = $json["content"] ?? [];

			\assert(\is_array($nodeContent));

			foreach ($nodeContent as $key => $item)
			{
				\assert(\is_array($item));

				$modifiedJson[$key] = $this->traverseNode($item);
			}

			return json_encode([
				...$json,
				"content" => $modifiedJson,
			], \JSON_THROW_ON_ERROR);
		}
		catch (\JsonException $e)
		{
			return $rawJson;
		}
	}

	private function traverseNode (array $node) : array
	{
		$content = $node["content"] ?? null;
		$marks = $node["marks"] ?? null;

		if (\is_array($content))
		{
			$modified = [];

			foreach ($content as $key => $childNode)
			{
				\assert(\is_array($childNode));

				$modified[$key] = $this->traverseNode($childNode);
			}

			return [
				...$node,
				"content" => $modified,
			];
		}

		if (\is_array($marks))
		{
			$modified = [];

			foreach ($marks as $key => $mark)
			{
				\assert(\is_array($mark));

				$modified[$key] = $this->traverseMark($mark);
			}

			return [
				...$node,
				"marks" => $modified,
			];
		}

		return $node;
	}

	private function traverseMark (array $mark) : array
	{
		switch ($mark["type"] ?? null)
		{
			case "link":
				$linkType = $mark["attrs"]["linktype"] ?? null;

				return match($linkType)
				{
					"story",
					"email",
					"url",
					"asset" => [
						...$mark,
						"attrs" => [
							"uuid" => null,
							"anchor" => null,
							...$mark["attrs"],
						],
					],

					default => $mark,
				};

			default:
				return $mark;
		}
	}
}
