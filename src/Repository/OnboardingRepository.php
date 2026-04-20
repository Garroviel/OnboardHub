<?php

namespace App\Repository;

use App\Entity\Onboarding;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Onboarding>
 */
class OnboardingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Onboarding::class);
    }

    //    /**
    //     * @return Onboarding[] Returns an array of Onboarding objects
    //     */
    public function findLatestForUserWithItems(User $user): ?Onboarding
	{
		return $this->createQueryBuilder('o')
			->leftJoin('o.tasks', 't')->addSelect('t')
			->leftJoin('o.trainings', 'tr')->addSelect('tr')
			->leftJoin('o.softwareItems', 's')->addSelect('s')
			->andWhere('o.user = :user')
			->setParameter('user', $user)
			->orderBy('o.createdAt', 'DESC')
			->addOrderBy('t.position', 'ASC')
			->addOrderBy('tr.position', 'ASC')
			->addOrderBy('s.position', 'ASC')
			->setMaxResults(1)
			->getQuery()
			->getOneOrNullResult();
	}

    //    public function findOneBySomeField($value): ?Onboarding
    //    {
    //        return $this->createQueryBuilder('o')
    //            ->andWhere('o.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
