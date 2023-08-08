<?php declare(strict_types=1);

namespace Torr\Storyblok\Field\Data\Table;

final class TableRowData
{
	/**
	 * @param TableColumnData[] $columns
	 */
	public function __construct (
		public readonly array $columns,
	) {}
}
