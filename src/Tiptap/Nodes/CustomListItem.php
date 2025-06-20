<?php declare(strict_types=1);

namespace Torr\Storyblok\Tiptap\Nodes;

use Tiptap\Nodes\ListItem;

final class CustomListItem extends ListItem
{
	#[\Override]
	public static function wrapper (mixed $DOMNode) : ?array
	{
		// Remove any wrapper elements that the PHP implementation would add by default if its children count is not 1.
		// The JS package of Tiptap does *not* add any wrapper elements as it's not necessary since all child elements
		// would create their own wrapper elements, e.g. `<p>`.
		//
		// This fixes nested `<p>`s within `<li>`s, which will cause rendering errors.
		return null;
	}
}
