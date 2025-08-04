<?php declare(strict_types=1);

namespace Torr\Storyblok\Tiptap\Helper;

class FixBrokenLinksMarksHelper
{
	/**
	 *
	 */
	public function fixLinksInDocument (array $document) : array
	{
		$modifiedJson = [];
		$nodeContent = $document["content"] ?? [];

		\assert(\is_array($nodeContent));

		foreach ($nodeContent as $key => $item)
		{
			\assert(\is_array($item));

			$modifiedJson[$key] = $this->traverseNode($item);
		}

		return [
			...$document,
			"content" => $modifiedJson,
		];
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
		$type = $mark["type"] ?? null;

		if ("link" !== $type)
		{
			return $mark;
		}

		return match ($mark["attrs"]["linktype"] ?? null)
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
	}
}
