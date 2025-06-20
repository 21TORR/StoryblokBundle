<?php declare(strict_types=1);

namespace Torr\Storyblok\Tiptap\Marks;

use Tiptap\Core\Mark;
use Tiptap\Utils\HTML;

class CustomLink extends Mark
{
	/** @var string */
	public static $name = "link";

	#[\Override]
	public function addOptions () : array
	{
		return [
			"HTMLAttributes" => [
				"target" => "_blank",
			],
		];
	}

	#[\Override]
	public function parseHTML () : array
	{
		return [
			[
				"tag" => "a[href]",
			],
		];
	}

	#[\Override]
	public function addAttributes () : array
	{
		return [
			"href" => [],
			"target" => [],
			"anchor" => [],
			"linktype" => [],
			"uuid" => [],
		];
	}

	#[\Override]
	public function renderHTML (mixed $mark, array $HTMLAttributes = []) : array
	{
		return [
			"a",
			HTML::mergeAttributes($this->options["HTMLAttributes"], $HTMLAttributes),
			0,
		];
	}
}
