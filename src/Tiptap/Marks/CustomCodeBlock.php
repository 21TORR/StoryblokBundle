<?php declare(strict_types=1);

namespace Torr\Storyblok\Tiptap\Marks;

use Tiptap\Nodes\CodeBlock;

final class CustomCodeBlock extends CodeBlock
{
	#[\Override]
	public function addAttributes () : array
	{
		return [
			...parent::addAttributes(),
			"class" => [],
		];
	}
}
