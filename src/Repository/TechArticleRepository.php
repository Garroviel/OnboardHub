<?php

namespace App\Repository;

use App\Entity\TechArticle;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


// Un repository est la classe chargée de rechercher une catégorie d’entités. 
// TechArticleRepository hérite des méthodes standards de Doctrine comme find, findBy et findOneBy. 
// Je peux aussi y ajouter des méthodes utilisant le QueryBuilder pour centraliser les requêtes métier plus complexes.

/**
 * @extends ServiceEntityRepository<TechArticle>
 */
class TechArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TechArticle::class);
		// Cette ligne signifie :
		// Ce repository est associé à l’entité TechArticle.
    }

    //    /**
    //     * @return TechArticle[] Returns an array of TechArticle objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('t.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?TechArticle
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
