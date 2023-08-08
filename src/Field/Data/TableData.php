<?php declare(strict_types=1);

namespace Torr\Storyblok\Field\Data;

use Torr\Storyblok\Field\Data\Table\TableRowData;

final class TableData
{
	public readonly TableRowData $thead;
	/** @var TableRowData[] */
	public readonly array $tbody;

	public function __construct (
		array $thead,
		array $tbody,
	)
	{
		$this->thead = new TableRowData($this->flattenRows($thead));
		$this->tbody = \array_map(
			fn (array $row) => new TableRowData($this->flattenRows($row["body"])),
			$tbody,
		);
	}

	private function flattenRows (array $row) : array
	{
		return \array_map(
			static fn (array $column) => $column["value"],
			$row,
		);
	}
}
