<?php declare(strict_types=1);

namespace Torr\Storyblok\Field\Data;

use Torr\Storyblok\Field\Data\Table\TableColumnData;

final class TableData
{
	/** @var TableColumnData[] */
	public readonly array $thead;

	/** @var list<TableColumnData[]> */
	public readonly array $tbody;

	public function __construct (
		array $thead,
		array $tbody,
	)
	{
		$this->thead = $this->flattenRows($thead);
		$this->tbody = array_map(
			$this->flattenRows(...),
			array_column($tbody, "body"),
		);
	}

	/**
	 * @return TableColumnData[]
	 */
	private function flattenRows (array $columnEntries) : array
	{
		return array_map(
			static fn (string $value) => new TableColumnData($value),
			array_column($columnEntries, "value"),
		);
	}
}
