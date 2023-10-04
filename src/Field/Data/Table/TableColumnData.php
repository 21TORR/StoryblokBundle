<?php declare(strict_types=1);

namespace Torr\Storyblok\Field\Data\Table;

final class TableColumnData
{
	public function __construct (
		public readonly string $content,
	) {}
}
