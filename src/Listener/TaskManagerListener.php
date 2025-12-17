<?php declare(strict_types=1);

namespace Torr\Storyblok\Listener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Torr\Storyblok\Assets\Task\AssetDataImporterTask;
use Torr\TaskManager\Event\RegisterTasksEvent;

class TaskManagerListener
{
	#[AsEventListener]
	public function onRegisterTasks (RegisterTasksEvent $event) : void
	{
		$event
			->register(new AssetDataImporterTask());
	}
}
