<?php declare(strict_types=1);

namespace Torr\Storyblok\Assets\Importer;

use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Torr\Storyblok\Api\Data\Asset\AssetData;
use Torr\Storyblok\Api\ManagementApi;
use Torr\Storyblok\Assets\Model\AssetDataEntityModel;
use Torr\Storyblok\Assets\Proxy\AssetProxy;
use Torr\Storyblok\Assets\Task\AssetDataImporterTask;
use Torr\Storyblok\Entity\AssetDataEntity;
use Torr\TaskManager\Director\TaskDirector;

final readonly class AssetDataImporterTaskHandler
{
	public function __construct (
		private TaskDirector $taskDirector,
		private ManagementApi $api,
		private ValidatorInterface $validator,
		private AssetDataEntityModel $assetModel,
		private AssetProxy $assetProxy,
	) {}

	/**
	 *
	 */
	#[AsMessageHandler]
	public function onImport (
		AssetDataImporterTask $task,
	) : void
	{
		$run = $this->taskDirector->startRun($task);
		$io = $run->getIo();

		$io->title("Storyblok: Import Asset Data");

		$io->writeln("• Fetching all assets ...");
		$apiAssets = $this->api->fetchAllAssets();

		$io->writeln("• Fetching existing entries from database ...");
		$existingAssets = $this->assetModel->fetchForImport();

		foreach ($existingAssets as $asset)
		{
			$asset->deleted = true;
		}

		$progress = $io->createProgressBar(\count($apiAssets));

		foreach ($apiAssets as $item)
		{
			$externalId = (string) $item->getId();
			$progress->setMessage($externalId);
			$progress->advance();

			$entity = $existingAssets[$externalId] ??= new AssetDataEntity($externalId);

			$this->mapToEntity($item, $entity);

			$errors = $this->validator->validate($entity);

			if (\count($errors) > 0)
			{
				$io->writeln(\sprintf(
					"• Import of asset '%s' failed: ",
					$externalId,
				));

				$io->listing(
					array_map(
						static fn (ConstraintViolationInterface $violation) => \sprintf(
							"<fg=yellow>[%s]</>: %s",
							$violation->getPropertyPath(),
							$violation->getMessage(),
						),
						iterator_to_array($errors),
					),
				);

				continue;
			}

			if ($entity->isNew())
			{
				$this->assetModel->add($entity);
			}
			else
			{
				$this->assetModel->update($entity);
			}
		}

		$progress->finish();
		$io->newLine();

		$io->writeln("• Flushing changes to the database");
		$this->assetModel->flush();

		$io->writeln("• Find all deleted assets and remove it from filesystem...");

		foreach ($existingAssets as $entity)
		{
			if (!$entity->deleted)
			{
				continue;
			}

			$this->assetProxy->clearStorageFile($entity->getAssetPath());
			$this->assetModel->remove($entity);
		}

		$io->writeln("• Flushing changes to the database");
		$this->assetModel->flush();

		$io->success("All done!");
		$run->finish(true);
	}

	/**
	 */
	private function mapToEntity (AssetData $raw, AssetDataEntity $entity) : AssetDataEntity
	{
		$entity->url = $raw->getUrl();
		$entity->tags = array_map(static fn($value) : string => $value["nane"], $raw->getInternalTags());
		$entity->private = $raw->isPrivate();
		$entity->deleted = false;

		return $entity;
	}
}
