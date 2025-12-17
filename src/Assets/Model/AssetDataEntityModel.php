<?php declare(strict_types=1);

namespace Torr\Storyblok\Assets\Model;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Torr\Rad\Model\EntityModel;
use Torr\Storyblok\Entity\AssetDataEntity;

/**
 * @final
 */
class AssetDataEntityModel extends EntityModel
{
	/** @var EntityRepository<AssetDataEntity> */
	private readonly EntityRepository $repository;

	/**
	 */
	public function __construct (EntityManagerInterface $registry)
	{
		parent::__construct($registry);

		$repository = $registry->getRepository(AssetDataEntity::class);
		$this->repository = $repository;
	}

	/**
	 * @return AssetDataEntity[]
	 */
	public function fetchForImport () : array
	{
		/** @var AssetDataEntity[] */
		return $this->repository->createQueryBuilder("asset", "asset.externalId")
			->select("asset")
			->getQuery()
			->getResult();
	}
}
