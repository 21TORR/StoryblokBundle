<?php declare(strict_types=1);

namespace Torr\Storyblok\Tiptap\Nodes;

use Tiptap\Nodes\OrderedList;

final class CustomOrderedList extends OrderedList
{
	#[\Override]
	public function addAttributes() : array
	{
		// We generally don't care about the `order` or `start` attribute
		return [];
	}
}
