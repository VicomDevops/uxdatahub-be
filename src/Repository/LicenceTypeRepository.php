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
class LicenceTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LicenceCategory::class);
    }

    /**
     * @return LicenceCategory|null
     */
    public function getNonDemoLicenceTypes()
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.title != :val')
            ->setParameter('val', 'demo')
            ->getQuery()
            ->getResult();
    }
    // /**
    //  * @return LicenceType[] Returns an array of LicenceType objects
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
    public function findOneBySomeField($value): ?LicenceType
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
