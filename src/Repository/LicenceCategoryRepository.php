<?php

namespace App\Repository;

use App\Entity\LicenceCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method LicenceCategory|null find($id, $lockMode = null, $lockVersion = null)
 * @method LicenceCategory|null findOneBy(array $criteria, array $orderBy = null)
 * @method LicenceCategory[]    findAll()
 * @method LicenceCategory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LicenceCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LicenceCategory::class);
    }

    // /**
    //  * @return LicenceCategory[] Returns an array of LicenceCategory objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('l.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?LicenceCategory
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
