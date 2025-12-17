<?php declare(strict_types=1);

namespace Torr\Storyblok\Assets\Task;

use Torr\TaskManager\Task\Task;
use Torr\TaskManager\Task\TaskMetaData;

final readonly class AssetDataImporterTask extends Task
{
	/**
	 */
	#[\Override]
	public function getMetaData () : TaskMetaData
	{
		return new TaskMetaData(
			label: "Import Asset Data",
			group: "Storyblok",
			uniqueTaskId: "storyblok.import-asset-data",
		);
	}
}
