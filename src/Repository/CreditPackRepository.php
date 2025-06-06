<?php

namespace App\Repository;

use App\Entity\CreditPack;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CreditPack|null find($id, $lockMode = null, $lockVersion = null)
 * @method CreditPack|null findOneBy(array $criteria, array $orderBy = null)
 * @method CreditPack[]    findAll()
 * @method CreditPack[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CreditPackRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CreditPack::class);
    }

    // /**
    //  * @return CreditPack[] Returns an array of CreditPack objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?CreditPack
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
